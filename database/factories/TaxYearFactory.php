<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\TaxYear>
 */
class TaxYearFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'year' => fake()->numberBetween(2024, 2035),
            'first_threshold_amount' => fake()->randomFloat(2, 1000000, 10000000),
            'second_threshold_amount' => fake()->randomFloat(2, 10000000, 20000000),
        ];
    }
}
