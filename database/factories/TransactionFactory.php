<?php

namespace Database\Factories;

use App\Models\Category;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Transaction>
 */
class TransactionFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'category_id' => Category::factory(),
            'user_id' => fn (array $attributes): int => (int) Category::query()->findOrFail($attributes['category_id'])->user_id,
            'client_id' => null,
            'invoice_id' => null,
            'date' => now()->toDateString(),
            'amount' => fake()->randomFloat(2, 100, 100000),
            'currency' => 'RSD',
            'title' => fake()->sentence(3),
            'note' => fake()->optional()->sentence(),
        ];
    }
}
