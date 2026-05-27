<?php

namespace Database\Factories;

use App\Models\HistoricoEstoque;
use App\Models\Peca;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class HistoricoEstoqueFactory extends Factory
{
    protected $model = HistoricoEstoque::class;

    public function definition(): array
    {
        $antes  = $this->faker->numberBetween(0, 100);
        $delta  = $this->faker->numberBetween(1, 20);
        $depois = $antes + $delta;

        return [
            'tenant_id'         => Tenant::factory(),
            'peca_id'           => Peca::factory(),
            'tipo'              => 'entrada',
            'quantidade_antes'  => $antes,
            'quantidade_depois' => $depois,
            'quantidade_delta'  => $delta,
            'referencia_tipo'   => 'entrada_estoque',
            'referencia_id'     => $this->faker->numberBetween(1, 100),
            'usuario_id'        => User::factory(),
            'created_at'        => now(), // $timestamps=false — setar explicitamente
        ];
    }
}
