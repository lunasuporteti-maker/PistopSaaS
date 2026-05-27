<?php

namespace App\Services;

use App\Models\Fornecedor;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class FornecedorService
{
    public function create(array $data): Fornecedor
    {
        $fornecedor = Fornecedor::create($data);
        $this->logAuditoria('created', $fornecedor);
        return $fornecedor;
    }

    public function update(Fornecedor $fornecedor, array $data): Fornecedor
    {
        $fornecedor->update($data);
        $this->logAuditoria('updated', $fornecedor);
        return $fornecedor->refresh();
    }

    public function archive(Fornecedor $fornecedor): void
    {
        $fornecedor->update(['ativo' => false]);
        $this->logAuditoria('archived', $fornecedor);
    }

    public function forceDelete(Fornecedor $fornecedor): void
    {
        // Verificar se há entradas vinculadas (tabela pode não existir ainda na migração)
        $temEntradas = false;
        if (class_exists(\App\Models\EntradaEstoque::class)) {
            $temEntradas = DB::table('entradas_estoque')
                ->where('fornecedor_id', $fornecedor->id)
                ->exists();
        }

        if ($temEntradas) {
            throw ValidationException::withMessages([
                'fornecedor' => 'Não é possível excluir este fornecedor pois existem entradas de estoque vinculadas. Use o arquivamento (ativo=false).',
            ]);
        }

        $this->logAuditoria('deleted', $fornecedor);
        $fornecedor->forceDelete();
    }

    private function logAuditoria(string $acao, Fornecedor $fornecedor): void
    {
        // Registrar no action_log se a tabela existir
        try {
            if (DB::getSchemaBuilder()->hasTable('action_logs')) {
                DB::table('action_logs')->insert([
                    'tenant_id'   => $fornecedor->tenant_id,
                    'user_id'     => auth()->id(),
                    'model_type'  => Fornecedor::class,
                    'model_id'    => $fornecedor->id,
                    'action'      => $acao,
                    'payload'     => json_encode($fornecedor->toArray()),
                    'created_at'  => now(),
                    'updated_at'  => now(),
                ]);
            }
        } catch (\Throwable $e) {
            // Log não é crítico — não bloquear operação
            logger()->warning("FornecedorService: audit log falhou — {$e->getMessage()}");
        }
    }
}
