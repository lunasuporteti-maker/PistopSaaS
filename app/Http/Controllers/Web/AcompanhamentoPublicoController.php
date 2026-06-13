<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Jobs\NotificarAprovacaoJob;
use App\Jobs\NotificarRejeicaoJob;
use App\Models\Configuracao;
use App\Models\Orcamento;
use App\Models\OrcamentoInteracao;
use App\Models\OrdemServico;
use App\Models\ServicoFoto;
use App\Services\OrcamentoService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class AcompanhamentoPublicoController extends Controller
{
    private array $etapas = [
        'orcamento' => ['icone' => '📋', 'titulo' => 'Orçamento em Análise',   'desc' => 'Estamos avaliando seu veículo e preparando o orçamento.', 'cor' => '#6c757d'],
        'aprovado' => ['icone' => '✅', 'titulo' => 'Aprovado — Aguardando Início', 'desc' => 'Orçamento aprovado! Nossa equipe iniciará o serviço em breve.', 'cor' => '#17a2b8'],
        'em_servico' => ['icone' => '🔧', 'titulo' => 'Serviço em Andamento',   'desc' => 'Seu veículo está em nossa oficina e o serviço está sendo realizado.', 'cor' => '#e67e22'],
        'concluido' => ['icone' => '🎉', 'titulo' => 'Serviço Concluído!',     'desc' => 'Seu veículo está pronto! Pode vir buscá-lo. Obrigado pela preferência!', 'cor' => '#28a745'],
        'cancelado' => ['icone' => '❌', 'titulo' => 'Serviço Cancelado',      'desc' => 'Este serviço foi cancelado. Entre em contato para mais informações.', 'cor' => '#dc3545'],
    ];

    /**
     * Resolve o orçamento a partir do token público.
     * Busca primeiro pela OS (novo fluxo), depois fallback no orçamento (legado AutoFix).
     * Reusado por show(), aprovar() e rejeitar().
     */
    private function resolveOrcamentoByToken(string $token): Orcamento
    {
        $os = OrdemServico::withoutGlobalScope('tenant')
            ->with(['orcamento.cliente', 'orcamento.veiculo', 'orcamento.servicos'])
            ->where('token_publico', $token)
            ->first();

        if ($os && $os->orcamento) {
            return $os->orcamento;
        }

        return Orcamento::withoutGlobalScope('tenant')
            ->with(['cliente', 'veiculo', 'servicos'])
            ->where('token_publico', $token)
            ->firstOrFail();
    }

    public function show(string $token)
    {
        // Busca OS pelo token primeiro (novo fluxo)
        $os = OrdemServico::withoutGlobalScope('tenant')
            ->with(['orcamento.cliente', 'orcamento.veiculo', 'orcamento.servicos'])
            ->where('token_publico', $token)
            ->first();

        if ($os) {
            $orcamento = $os->orcamento;
            $status = $os->status;
        } else {
            // Fallback: token antigo em orcamentos (legado AutoFix)
            $orcamento = Orcamento::withoutGlobalScope('tenant')
                ->with(['cliente', 'veiculo', 'servicos'])
                ->where('token_publico', $token)
                ->firstOrFail();
            $status = $orcamento->status;
        }

        $etapa = $this->etapas[$status] ?? $this->etapas['orcamento'];
        $etapas = $this->etapas;
        $ordem = ['orcamento', 'aprovado', 'em_servico', 'concluido'];
        $posAtual = array_search($status, $ordem);

        // Identidade da oficina (portal público — sem tenant bound, busca pelo tenant do orçamento)
        $nomeOficina     = Configuracao::getForTenant($orcamento->tenant_id, 'nome_oficina', 'PitStop');
        $telefoneOficina = Configuracao::getForTenant($orcamento->tenant_id, 'telefone_oficina', '');

        // Galeria de fotos (Story 2.6)
        $fotos = ServicoFoto::withoutGlobalScope('tenant')
            ->where('orcamento_id', $orcamento->id)
            ->whereNull('deleted_at')
            ->orderBy('categoria')
            ->orderBy('created_at')
            ->get()
            ->map(fn ($f) => [
                'url_thumb'    => $f->path_thumbnail
                    ? Storage::disk('public')->url($f->path_thumbnail)
                    : Storage::disk('public')->url($f->path_original),
                'url_original' => route('acompanhar.foto', [$token, $f->id]),
                'categoria'    => $f->categoria,
                'legenda'      => $f->legenda,
            ]);

        return response()
            ->view('pitstop.acompanhamento', compact('orcamento', 'etapa', 'etapas', 'ordem', 'posAtual', 'token', 'fotos', 'nomeOficina', 'telefoneOficina'))
            ->header('Cache-Control', 'no-store, no-cache, must-revalidate, max-age=0')
            ->header('Pragma', 'no-cache')
            ->header('Expires', '0');
    }

    /**
     * Aprovação online do orçamento pelo cliente (portal público).
     * FR-001 a FR-007, FR-050.
     */
    public function aprovar(Request $request, string $token): RedirectResponse
    {
        $orcamento = $this->resolveOrcamentoByToken($token);

        // Guard: só aprova orçamento ainda aguardando aprovação (idempotência + FR-006/FR-007)
        if ($orcamento->status !== 'orcamento') {
            return redirect()
                ->route('acompanhar.publico', $token)
                ->with('warning', 'Este orçamento não está mais disponível para aprovação.');
        }

        // AC2: aceite de termos obrigatório
        $request->validate([
            'aceite_termos' => 'accepted',
        ], [
            'aceite_termos.accepted' => 'Você precisa marcar que leu e aceita os itens do orçamento.',
        ]);

        $ip = $request->ip();
        $userAgent = (string) $request->userAgent();

        DB::transaction(function () use ($orcamento, $ip, $userAgent) {
            // AC3: atualiza campos de aprovação (NÃO altera valor_total nem itens — AC10)
            $orcamento->update([
                'status' => 'aprovado',
                'aprovado_em' => now(),
                'aprovado_por_canal' => Orcamento::CANAL_PORTAL,
                'aprovado_ip' => $ip,
                'aprovado_user_agent' => Str::limit($userAgent, 500, ''),
            ]);

            // AC4: registro de interação (evidência legal), usuario_id = NULL (cliente externo)
            OrcamentoInteracao::create([
                'tenant_id' => $orcamento->tenant_id,
                'orcamento_id' => $orcamento->id,
                'tipo' => OrcamentoInteracao::TIPO_APROVACAO,
                'dados_json' => [
                    'ip' => $ip,
                    'user_agent' => $userAgent,
                    'aceite_termos' => true,
                    'timestamp' => now()->toIso8601String(),
                ],
                'usuario_id' => null,
            ]);

            // Gera a OS automaticamente a partir do orçamento aprovado
            app(OrcamentoService::class)->gerarOs($orcamento);
        });

        // AC5: notificação interna (fora da transação — efeito colateral assíncrono)
        NotificarAprovacaoJob::dispatch($orcamento->id);

        return redirect()
            ->route('acompanhar.publico', $token)
            ->with('success', 'Orçamento aprovado! Em breve seu veículo entrará em serviço.');
    }

    /**
     * Solicitação de revisão / rejeição do orçamento pelo cliente (portal público).
     * FR-004, FR-031, FR-050. NÃO altera o status do orçamento.
     */
    public function rejeitar(Request $request, string $token): RedirectResponse
    {
        $orcamento = $this->resolveOrcamentoByToken($token);

        // Só faz sentido pedir revisão enquanto está como orçamento
        if ($orcamento->status !== 'orcamento') {
            return redirect()
                ->route('acompanhar.publico', $token)
                ->with('warning', 'Este orçamento não está mais disponível para solicitação de revisão.');
        }

        // AC8: validação de tamanho mínimo no backend (retorna 422 ao falhar)
        $validated = $request->validate([
            'motivo' => 'required|string|min:10|max:2000',
        ], [
            'motivo.required' => 'Por favor, descreva o motivo da revisão.',
            'motivo.min' => 'O motivo deve ter no mínimo 10 caracteres.',
        ]);

        // AC7: sanitização contra XSS (Risco R6) — texto puro
        $motivo = trim(strip_tags($validated['motivo']));
        $ip = $request->ip();
        $userAgent = (string) $request->userAgent();

        // AC3: rejeição NÃO altera o status do orçamento (permanece 'orcamento')
        OrcamentoInteracao::create([
            'tenant_id' => $orcamento->tenant_id,
            'orcamento_id' => $orcamento->id,
            'tipo' => OrcamentoInteracao::TIPO_REJEICAO,
            'dados_json' => [
                'motivo' => $motivo,
                'ip' => $ip,
                'user_agent' => $userAgent,
                'orcamento_numero' => '#'.$orcamento->id,
                'timestamp' => now()->toIso8601String(),
            ],
            'usuario_id' => null,
        ]);

        // AC5: notificação interna com motivo truncado
        NotificarRejeicaoJob::dispatch($orcamento->id, $motivo);

        return redirect()
            ->route('acompanhar.publico', $token)
            ->with('info', 'Sua observação foi enviada. A oficina entrará em contato em breve.');
    }

    /** Serve arquivo de foto validando que o token pertence ao orçamento (Story 2.6) */
    public function servirFoto(string $token, ServicoFoto $foto)
    {
        $orcamento = $this->resolveOrcamentoByToken($token);
        abort_if($foto->orcamento_id !== $orcamento->id, 403);

        return Storage::disk('public')->response($foto->path_original);
    }
}
