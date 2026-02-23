<?php

namespace Database\Factories;

use App\Models\IssueCategory;
use App\Models\IssuePriority;
use App\Models\IssueStatus;
use App\Models\Project;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Issue>
 */
class IssueFactory extends Factory
{
    public function definition(): array
    {
        return [
            'project_id' => Project::factory(),
            'client_id' => null,
            'client_contact_id' => null,
            'status_id' => IssueStatus::factory(),
            'priority_id' => IssuePriority::factory(),
            'category_id' => IssueCategory::factory(),
            'author_id' => User::factory(),
            'assignee_id' => null,
            'title' => fake()->sentence(5),
            'description' => fake()->paragraph(),
            'due_date' => fake()->optional()->date(),
            'completed_at' => null,
        ];
    }
}
