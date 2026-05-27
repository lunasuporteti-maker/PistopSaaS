<?php

namespace Database\Factories;

use App\Models\Peca;
use App\Models\Tenant;
use Illuminate\Database\Eloquent\Factories\Factory;

class PecaFactory extends Factory
{
    protected $model = Peca::class;

    public function definition(): array
    {
        return [
            'tenant_id'      => Tenant::factory(),
            'nome'           => $this->faker->words(3, true),
            'quantidade'     => $this->faker->numberBetween(0, 100),
            'preco_custo'    => $this->faker->randomFloat(2, 5, 200),
            'preco_venda'    => $this->faker->randomFloat(2, 10, 500),
            'estoque_minimo' => $this->faker->numberBetween(1, 10),
        ];
    }
}
