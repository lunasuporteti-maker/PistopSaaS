<?php

namespace Database\Factories;

use App\Models\EntradaEstoque;
use App\Models\EntradaEstoqueItem;
use App\Models\Peca;
use App\Models\Tenant;
use Illuminate\Database\Eloquent\Factories\Factory;

class EntradaEstoqueItemFactory extends Factory
{
    protected $model = EntradaEstoqueItem::class;

    public function definition(): array
    {
        $quantidade = $this->faker->numberBetween(1, 50);
        $precoCusto = $this->faker->randomFloat(2, 5, 500);

        return [
            'tenant_id'            => Tenant::factory(),
            'entrada_id'           => EntradaEstoque::factory(),
            'peca_id'              => Peca::factory(),
            'quantidade'           => $quantidade,
            'preco_custo_unitario' => $precoCusto,
            'subtotal'             => round($quantidade * $precoCusto, 2),
        ];
    }
}
