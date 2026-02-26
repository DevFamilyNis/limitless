<?php

namespace Database\Factories;

use App\Models\BillingPeriod;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\MonthlyExpenseItem>
 */
class MonthlyExpenseItemFactory extends Factory
{
    /**
     * Define the model's default state.
     *
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
            'title' => fake()->words(3, true),
            'amount' => fake()->randomFloat(2, 1000, 100000),
            'note' => fake()->optional()->sentence(),
        ];
    }
}
