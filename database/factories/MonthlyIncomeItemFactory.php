<?php

namespace Database\Factories;

use App\Models\BillingPeriod;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\MonthlyIncomeItem>
 */
class MonthlyIncomeItemFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $monthlyBillingPeriodId = BillingPeriod::query()
            ->where('key', 'monthly')
            ->value('id');

        return [
            'user_id' => User::factory(),
            'billing_period_id' => $monthlyBillingPeriodId,
            'name' => fake()->words(3, true),
            'price' => fake()->randomFloat(2, 1000, 100000),
            'description' => fake()->optional()->sentence(),
        ];
    }
}
