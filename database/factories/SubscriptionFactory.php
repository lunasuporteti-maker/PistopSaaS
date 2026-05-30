<?php

namespace Database\Factories;

use App\Models\Subscription;
use App\Models\Tenant;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Subscription>
 */
class SubscriptionFactory extends Factory
{
    protected $model = Subscription::class;

    public function definition(): array
    {
        return [
            'tenant_id' => Tenant::factory(),
            'plano' => Subscription::PLANO_PADRAO,
            'status' => Subscription::STATUS_ACTIVE,
            'gateway' => Subscription::GATEWAY_ASAAS,
            'gateway_customer_id' => 'cus_'.$this->faker->unique()->bothify('############'),
        ];
    }

    /** Tenant legado/manual: sem gateway_customer_id */
    public function legado(): static
    {
        return $this->state(fn () => [
            'gateway' => Subscription::GATEWAY_MANUAL,
            'gateway_customer_id' => null,
        ]);
    }
}
