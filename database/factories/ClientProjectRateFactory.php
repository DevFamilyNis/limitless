<?php

namespace Database\Factories;

use App\Models\BillingPeriod;
use App\Models\Client;
use App\Models\Project;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\ClientProjectRate>
 */
class ClientProjectRateFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $billingPeriodId = BillingPeriod::query()
            ->where('key', 'monthly')
            ->value('id');

        return [
            'client_id' => Client::factory(),
            'project_id' => Project::factory(),
            'billing_period_id' => $billingPeriodId,
            'price_amount' => fake()->randomFloat(2, 1000, 200000),
            'currency' => 'RSD',
            'is_active' => true,
        ];
    }
}
