<?php

namespace Database\Factories;

use App\Models\EntradaEstoque;
use App\Models\Fornecedor;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class EntradaEstoqueFactory extends Factory
{
    protected $model = EntradaEstoque::class;

    public function definition(): array
    {
        $tenant = Tenant::factory()->create();

        return [
            'tenant_id'      => $tenant->id,
            'numero_entrada' => 'ENT-' . now()->year . '-' . str_pad($this->faker->unique()->numberBetween(1, 9999), 4, '0', STR_PAD_LEFT),
            'fornecedor_id'  => Fornecedor::factory()->create(['tenant_id' => $tenant->id])->id,
            'usuario_id'     => User::factory()->create(['tenant_id' => $tenant->id])->id,
            'data_entrada'   => $this->faker->dateTimeBetween('-6 months', 'now')->format('Y-m-d'),
            'numero_nota'    => $this->faker->optional()->numerify('NF-#####'),
            'tipo_documento' => $this->faker->randomElement(['nota_manual', 'cupom', 'nfe', 'sem_documento']),
            'valor_total'    => $this->faker->randomFloat(2, 50, 5000),
            'status'         => 'ativa',
            'observacoes'    => $this->faker->optional()->sentence(),
            'anexo_path'     => null,
            'cancelado_por'  => null,
            'cancelado_em'   => null,
            'cancelado_motivo' => null,
        ];
    }

    public function cancelada(): static
    {
        return $this->state(fn (array $attributes) => [
            'status'          => 'cancelada',
            'cancelado_por'   => $attributes['usuario_id'],
            'cancelado_em'    => now(),
            'cancelado_motivo' => 'Erro na digitação',
        ]);
    }
}
