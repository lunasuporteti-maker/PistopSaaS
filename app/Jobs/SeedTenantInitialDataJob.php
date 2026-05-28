<?php

namespace App\Jobs;

use App\Models\Cliente;
use App\Models\Funcionario;
use App\Models\Peca;
use App\Models\Tenant;
use App\Models\Veiculo;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class SeedTenantInitialDataJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;
    public array $backoff = [60, 300, 900];

    public function __construct(public readonly int $tenantId) {}

    public function handle(): void
    {
        $tenant = Tenant::find($this->tenantId);

        if (! $tenant) {
            Log::warning("SeedTenantInitialDataJob: tenant {$this->tenantId} não encontrado.");
            return;
        }

        // Evita duplicação se o job rodar mais de uma vez
        if (Cliente::withoutGlobalScope('tenant')->where('tenant_id', $this->tenantId)->where('is_example', true)->exists()) {
            return;
        }

        // Vincula o tenant ao container para que o trait BelongsToTenant funcione
        app()->instance(Tenant::class, $tenant);

        $cliente = Cliente::create([
            'tenant_id'  => $this->tenantId,
            'is_example' => true,
            'nome'       => 'Cliente Exemplo',
            'telefone'   => '(81) 99999-0000',
        ]);

        Veiculo::create([
            'tenant_id'  => $this->tenantId,
            'is_example' => true,
            'cliente_id' => $cliente->id,
            'modelo'     => 'Veículo Exemplo',
            'placa'      => 'ABC-0000',
            'marca'      => 'Exemplo',
        ]);

        $pecas = [
            'Óleo Motor 5W30',
            'Filtro de Óleo',
            'Filtro de Ar',
            'Vela de Ignição',
            'Pastilha de Freio Dianteira',
        ];

        foreach ($pecas as $nome) {
            Peca::create([
                'tenant_id'      => $this->tenantId,
                'is_example'     => true,
                'nome'           => $nome,
                'quantidade'     => 0,
                'preco_custo'    => 0,
                'preco_venda'    => 0,
                'estoque_minimo' => 0,
            ]);
        }

        Funcionario::create([
            'tenant_id'    => $this->tenantId,
            'is_example'   => true,
            'nome'         => 'Mecânico Exemplo',
            'cargo'        => 'Mecânico',
            'salario_base' => 0,
            'ativo'        => true,
        ]);
    }

    public function failed(\Throwable $e): void
    {
        Log::error("SeedTenantInitialDataJob falhou para tenant {$this->tenantId}: {$e->getMessage()}");
    }
}
