<?php

namespace Database\Factories;

use App\Domain\Contract\Enums\ContractStatus;
use App\Domain\Contract\Enums\ContractType;
use App\Models\Client;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Contract>
 */
class ContractFactory extends Factory
{
    public function definition(): array
    {
        $user = User::factory()->create();

        return [
            'user_id' => $user->id,
            'client_id' => Client::factory()->create(['user_id' => $user->id])->id,
            'parent_id' => null,
            'type' => ContractType::Ugovor->value,
            'status' => ContractStatus::Aktivan->value,
            'start_date' => fake()->dateTimeBetween('-2 years', 'now')->format('Y-m-d'),
            'end_date' => null,
            'note' => null,
        ];
    }

    public function aneks(): static
    {
        return $this->state(fn (array $attributes) => [
            'type' => ContractType::Aneks->value,
        ]);
    }

    public function neaktivan(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => ContractStatus::Neaktivan->value,
        ]);
    }
}
