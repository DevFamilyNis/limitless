<?php

namespace Database\Factories;

use App\Models\LeadCampaign;
use App\Models\LeadStatus;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Lead>
 */
class LeadFactory extends Factory
{
    public function definition(): array
    {
        return [
            'lead_campaign_id' => LeadCampaign::factory(),
            'lead_status_id' => LeadStatus::factory(),
            'company_name' => fake()->company(),
            'email' => fake()->safeEmail(),
            'phone' => fake()->phoneNumber(),
            'last_contacted_at' => null,
            'last_contact_method' => null,
            'last_response_at' => null,
            'next_follow_up_at' => null,
            'converted_at' => null,
        ];
    }
}
