<?php

namespace App\Services;

use App\Models\EntradaEstoque;
use App\Models\EntradaEstoqueItem;
use App\Models\HistoricoEstoque;
use App\Models\Peca;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\ValidationException;

class EntradaEstoqueService
{
    // ─────────────────────────────────────────────────────────────
    // Ponto de entrada principal
    // ─────────────────────────────────────────────────────────────

    /**
     * Cria uma entrada de estoque em transação atômica.
     * Incrementa estoque, atualiza último custo e registra historico.
     */
    public function criar(array $data, ?UploadedFile $anexo = null): EntradaEstoque
    {
        $tenantId = app(\App\Models\Tenant::class)->id;

        return DB::transaction(function () use ($data, $anexo, $tenantId) {
            // Gerar número sequencial dentro da transação (protegido por lock)
            $numeroEntrada = $this->gerarNumeroEntrada($tenantId);

            $valorTotal = 0;
            $itensProcessados = [];

            foreach ($data['itens'] as $item) {
                // Lock para evitar race condition — no-op em SQLite (testes), efetivo no PG/MySQL
                $peca = Peca::lockForUpdate()->findOrFail($item['peca_id']);

                $quantidade  = (int) $item['quantidade'];
                $precoCusto  = (float) $item['preco_custo_unitario'];
                $subtotal    = round($quantidade * $precoCusto, 2);
                $qtdAntes    = $peca->quantidade;

                // Incrementar estoque
                $peca->increment('quantidade', $quantidade);

                // Atualizar preço de custo (estratégia último custo)
                $peca->update(['preco_custo' => $precoCusto]);

                $valorTotal += $subtotal;

                $itensProcessados[] = [
                    'peca'       => $peca,
                    'item_data'  => [
                        'tenant_id'            => $tenantId,
                        'peca_id'              => $peca->id,
                        'quantidade'           => $quantidade,
                        'preco_custo_unitario' => $precoCusto,
                        'subtotal'             => $subtotal,
                    ],
                    'qtd_antes'  => $qtdAntes,
                    'qtd_depois' => $qtdAntes + $quantidade,
                ];
            }

            // Criar registro principal
            $entrada = EntradaEstoque::create([
                'tenant_id'      => $tenantId,
                'numero_entrada' => $numeroEntrada,
                'fornecedor_id'  => $data['fornecedor_id'],
                'usuario_id'     => auth()->id(),
                'data_entrada'   => $data['data_entrada'] ?? now()->toDateString(),
                'numero_nota'    => $data['numero_nota'] ?? null,
                'tipo_documento' => $data['tipo_documento'] ?? 'nota_manual',
                'valor_total'    => round($valorTotal, 2),
                'status'         => 'ativa',
                'observacoes'    => $data['observacoes'] ?? null,
            ]);

            // Criar itens e historico APÓS ter o ID da entrada
            foreach ($itensProcessados as $processado) {
                EntradaEstoqueItem::create(array_merge(
                    $processado['item_data'],
                    ['entrada_id' => $entrada->id]
                ));

                HistoricoEstoque::create([
                    'tenant_id'         => $tenantId,
                    'peca_id'           => $processado['peca']->id,
                    'tipo'              => 'entrada',
                    'quantidade_antes'  => $processado['qtd_antes'],
                    'quantidade_depois' => $processado['qtd_depois'],
                    'quantidade_delta'  => $processado['item_data']['quantidade'],
                    'referencia_tipo'   => 'entrada_estoque',
                    'referencia_id'     => $entrada->id,
                    'usuario_id'        => auth()->id(),
                    'created_at'        => now(),
                ]);
            }

            // Processar anexo (fora da loop de itens, ainda dentro da transação)
            if ($anexo) {
                $path = $this->processarAnexo($anexo, $tenantId, $entrada->id);
                $entrada->update(['anexo_path' => $path]);
            }

            $this->logAuditoria($entrada);

            return $entrada->load(['itens.peca', 'fornecedor', 'usuario']);
        });
    }

    // ─────────────────────────────────────────────────────────────
    // Geração de número sequencial
    // ─────────────────────────────────────────────────────────────

    /**
     * Gera próximo número de entrada no formato ENT-{ano}-{seq padded 4}.
     * Deve ser chamado dentro de uma transação para proteger contra concorrência.
     */
    public function gerarNumeroEntrada(int $tenantId): string
    {
        $ano = now()->year;

        // Busca o maior número do ano atual para este tenant
        $ultimo = EntradaEstoque::withoutGlobalScope('tenant')
            ->where('tenant_id', $tenantId)
            ->whereYear('created_at', $ano)
            ->lockForUpdate()
            ->max('numero_entrada'); // string 'ENT-2026-0042' ou null

        $seq = 1;
        if ($ultimo) {
            // Extrai sequência: 'ENT-2026-0042' → 42 → próximo = 43
            $partes = explode('-', $ultimo);
            $seq = (int) end($partes) + 1;
        }

        return 'ENT-' . $ano . '-' . str_pad($seq, 4, '0', STR_PAD_LEFT);
    }

    // ─────────────────────────────────────────────────────────────
    // Upload de anexo
    // ─────────────────────────────────────────────────────────────

    /**
     * Valida e armazena o anexo da entrada.
     * Retorna o path relativo (ex: tenants/1/entradas/1_abc123.pdf).
     */
    public function processarAnexo(UploadedFile $file, int $tenantId, int $entradaId): string
    {
        $permitidos = ['application/pdf', 'image/jpeg', 'image/png'];
        $mimeType   = $file->getMimeType();

        if (! in_array($mimeType, $permitidos)) {
            throw ValidationException::withMessages([
                'anexo' => 'Tipo de arquivo não permitido. Use PDF, JPG ou PNG.',
            ]);
        }

        if ($file->getSize() > 5 * 1024 * 1024) {
            throw ValidationException::withMessages([
                'anexo' => 'O anexo não pode ultrapassar 5MB.',
            ]);
        }

        $extensao  = $file->getClientOriginalExtension();
        $hash      = substr(md5(uniqid('', true)), 0, 8);
        $nomeArq   = "{$entradaId}_{$hash}.{$extensao}";
        $diretorio = "tenants/{$tenantId}/entradas";

        return $file->storeAs($diretorio, $nomeArq, 'local');
    }

    // ─────────────────────────────────────────────────────────────
    // Cancelamento
    // ─────────────────────────────────────────────────────────────

    /**
     * Cancela uma entrada de estoque em transação atômica.
     * Reverte o estoque de cada item e registra no historico.
     *
     * @throws ValidationException se entrada já cancelada ou decrement geraria saldo negativo
     */
    public function cancelar(EntradaEstoque $entrada, string $motivo, int $userId): void
    {
        if ($entrada->isCancelada()) {
            throw ValidationException::withMessages([
                'status' => 'Esta entrada já foi cancelada.',
            ]);
        }

        // Carregar itens com suas peças para o pre-check
        $entrada->loadMissing('itens.peca');

        // Pre-check anti-negativo ANTES de iniciar a transação
        $erros = [];
        foreach ($entrada->itens as $item) {
            $peca = $item->peca;
            if ($peca && ($peca->quantidade - $item->quantidade) < 0) {
                $erros[] = "Peça '{$peca->nome}' (atual: {$peca->quantidade}, tentativa: -{$item->quantidade})";
            }
        }

        if (! empty($erros)) {
            throw ValidationException::withMessages([
                'estoque' => 'Cancelamento bloqueado — estoque insuficiente: ' . implode('; ', $erros),
            ]);
        }

        $tenantId = $entrada->tenant_id;

        DB::transaction(function () use ($entrada, $motivo, $userId, $tenantId) {
            foreach ($entrada->itens as $item) {
                // Lock para evitar race condition no decrement
                $peca   = Peca::lockForUpdate()->findOrFail($item->peca_id);
                $antes  = $peca->quantidade;
                $depois = $antes - $item->quantidade;

                $peca->decrement('quantidade', $item->quantidade);

                HistoricoEstoque::create([
                    'tenant_id'         => $tenantId,
                    'peca_id'           => $peca->id,
                    'tipo'              => 'cancelamento',
                    'quantidade_antes'  => $antes,
                    'quantidade_depois' => $depois,
                    'quantidade_delta'  => -$item->quantidade,
                    'referencia_tipo'   => 'entrada_estoque',
                    'referencia_id'     => $entrada->id,
                    'usuario_id'        => $userId,
                    'created_at'        => now(),
                ]);
            }

            $entrada->update([
                'status'           => 'cancelada',
                'cancelado_por'    => $userId,
                'cancelado_em'     => now(),
                'cancelado_motivo' => $motivo,
            ]);
        });

        $this->logAuditoria($entrada->fresh());
    }

    // ─────────────────────────────────────────────────────────────
    // Log de auditoria
    // ─────────────────────────────────────────────────────────────

    private function logAuditoria(EntradaEstoque $entrada): void
    {
        try {
            if (DB::getSchemaBuilder()->hasTable('action_logs')) {
                DB::table('action_logs')->insert([
                    'tenant_id'  => $entrada->tenant_id,
                    'user_id'    => auth()->id(),
                    'model_type' => EntradaEstoque::class,
                    'model_id'   => $entrada->id,
                    'action'     => 'created',
                    'payload'    => json_encode($entrada->toArray()),
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        } catch (\Throwable $e) {
            logger()->warning("EntradaEstoqueService: audit log falhou — {$e->getMessage()}");
        }
    }
}
