<?php

namespace Database\Factories;

use App\Models\Tenant;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Tenant>
 */
class TenantFactory extends Factory
{
    protected $model = Tenant::class;

    public function definition(): array
    {
        return [
            'nome'  => $this->faker->company(),
            'slug'  => $this->faker->unique()->slug(2),
            'ativo' => true,
            'plano' => 'basico',
        ];
    }
}
