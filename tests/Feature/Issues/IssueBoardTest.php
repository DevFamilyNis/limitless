<?php

use App\Livewire\Issues\Board;
use App\Models\Issue;
use App\Models\IssueCategory;
use App\Models\IssuePriority;
use App\Models\IssueStatus;
use App\Models\Project;
use App\Models\User;
use Database\Seeders\IssueDictionarySeeder;
use Livewire\Livewire;

test('issue board page is displayed', function () {
    $user = User::factory()->create();
    Project::factory()->create(['user_id' => $user->id]);

    $this->seed(IssueDictionarySeeder::class);

    $this->actingAs($user)
        ->get(route('issues.board'))
        ->assertOk();
});

test('move issue to done sets completed at', function () {
    $user = User::factory()->create();
    $project = Project::factory()->create(['user_id' => $user->id]);

    $this->seed(IssueDictionarySeeder::class);

    $backlog = IssueStatus::query()->where('key', 'backlog')->firstOrFail();
    $done = IssueStatus::query()->where('key', 'done')->firstOrFail();
    $priority = IssuePriority::query()->where('key', 'medium')->firstOrFail();
    $category = IssueCategory::query()->where('name', 'Task')->firstOrFail();

    $issue = Issue::query()->create([
        'project_id' => $project->id,
        'client_id' => null,
        'client_contact_id' => null,
        'status_id' => $backlog->id,
        'priority_id' => $priority->id,
        'category_id' => $category->id,
        'author_id' => $user->id,
        'assignee_id' => null,
        'title' => 'Test issue',
        'description' => null,
        'due_date' => null,
        'completed_at' => null,
    ]);

    Livewire::actingAs($user)->test(Board::class)
        ->call('moveIssue', $issue->id, $done->id);

    $issue->refresh();

    expect($issue->status_id)->toBe($done->id);
    expect($issue->completed_at)->not()->toBeNull();
});
