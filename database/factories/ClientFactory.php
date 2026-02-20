<?php

namespace Database\Factories;

use App\Models\ClientType;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Client>
 */
class ClientFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $clientTypeId = ClientType::query()
            ->where('key', 'person')
            ->value('id');

        return [
            'user_id' => User::factory(),
            'client_type_id' => $clientTypeId,
            'display_name' => fake()->company(),
            'email' => fake()->safeEmail(),
            'phone' => fake()->phoneNumber(),
            'address' => fake()->address(),
            'note' => fake()->sentence(),
            'is_active' => true,
        ];
    }
}
