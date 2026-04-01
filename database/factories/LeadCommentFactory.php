<?php

namespace Database\Factories;

use App\Models\Lead;
use App\Models\LeadStatus;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\LeadComment>
 */
class LeadCommentFactory extends Factory
{
    public function definition(): array
    {
        return [
            'lead_id' => Lead::factory(),
            'author_id' => User::factory(),
            'lead_status_id' => LeadStatus::factory(),
            'event_type' => fake()->randomElement(['note', 'call', 'email']),
            'contact_method' => fake()->randomElement(['phone', 'email', 'whatsapp']),
            'outcome' => fake()->randomElement(['answered', 'no_answer', 'interested']),
            'body' => fake()->paragraph(),
            'contacted_at' => now(),
            'responded_at' => null,
            'next_follow_up_at' => null,
        ];
    }
}
