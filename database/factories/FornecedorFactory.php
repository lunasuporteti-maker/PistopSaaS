<?php

namespace Database\Factories;

use App\Models\Fornecedor;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Fornecedor>
 */
class FornecedorFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'tenant_id'   => 1,
            'nome'        => $this->faker->company(),
            'cnpj'        => null,
            'telefone'    => $this->faker->phoneNumber(),
            'email'       => $this->faker->companyEmail(),
            'endereco'    => $this->faker->address(),
            'observacoes' => null,
            'ativo'       => true,
        ];
    }

    public function arquivado(): static
    {
        return $this->state(fn (array $attributes) => ['ativo' => false]);
    }
}
