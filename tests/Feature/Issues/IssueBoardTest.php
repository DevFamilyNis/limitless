<?php

use App\Livewire\Issues\Index;
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
        ->get(route('issues.index'))
        ->assertOk()
        ->assertSee('Kanban');
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

    Livewire::actingAs($user)->test(Index::class)
        ->call('moveIssue', $issue->id, $done->id);

    $issue->refresh();

    expect($issue->status_id)->toBe($done->id);
    expect($issue->completed_at)->not()->toBeNull();
});

test('issues are visible to another user in shared workspace', function () {
    $owner = User::factory()->create();
    $anotherUser = User::factory()->create();
    $project = Project::factory()->create(['user_id' => $owner->id]);

    $this->seed(IssueDictionarySeeder::class);

    $backlog = IssueStatus::query()->where('key', 'backlog')->firstOrFail();
    $priority = IssuePriority::query()->where('key', 'medium')->firstOrFail();
    $category = IssueCategory::query()->where('name', 'Task')->firstOrFail();

    Issue::query()->create([
        'project_id' => $project->id,
        'client_id' => null,
        'client_contact_id' => null,
        'status_id' => $backlog->id,
        'priority_id' => $priority->id,
        'category_id' => $category->id,
        'author_id' => $owner->id,
        'assignee_id' => null,
        'title' => 'Shared issue title',
        'description' => null,
        'due_date' => null,
        'completed_at' => null,
    ]);

    $this->actingAs($anotherUser)
        ->get(route('issues.index'))
        ->assertOk()
        ->assertSee('Shared issue title');
});

test('issue details page can be opened', function () {
    $user = User::factory()->create();
    $project = Project::factory()->create(['user_id' => $user->id]);

    $this->seed(IssueDictionarySeeder::class);

    $backlog = IssueStatus::query()->where('key', 'backlog')->firstOrFail();
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
        'title' => 'Issue details page',
        'description' => null,
        'due_date' => null,
        'completed_at' => null,
    ]);

    $this->actingAs($user)
        ->get(route('issues.show', $issue))
        ->assertOk()
        ->assertSee('Issue details page');
});

test('issues index defaults to all projects', function () {
    $user = User::factory()->create();
    $projectA = Project::factory()->create(['user_id' => $user->id, 'name' => 'Alpha Project']);
    $projectB = Project::factory()->create(['user_id' => $user->id, 'name' => 'Beta Project']);

    $this->seed(IssueDictionarySeeder::class);

    $backlog = IssueStatus::query()->where('key', 'backlog')->firstOrFail();
    $priority = IssuePriority::query()->where('key', 'medium')->firstOrFail();
    $category = IssueCategory::query()->where('name', 'Task')->firstOrFail();

    Issue::query()->create([
        'project_id' => $projectA->id,
        'client_id' => null,
        'client_contact_id' => null,
        'status_id' => $backlog->id,
        'priority_id' => $priority->id,
        'category_id' => $category->id,
        'author_id' => $user->id,
        'assignee_id' => null,
        'title' => 'Alpha task',
        'description' => null,
        'due_date' => null,
        'completed_at' => null,
    ]);

    Issue::query()->create([
        'project_id' => $projectB->id,
        'client_id' => null,
        'client_contact_id' => null,
        'status_id' => $backlog->id,
        'priority_id' => $priority->id,
        'category_id' => $category->id,
        'author_id' => $user->id,
        'assignee_id' => null,
        'title' => 'Beta task',
        'description' => null,
        'due_date' => null,
        'completed_at' => null,
    ]);

    $this->actingAs($user)
        ->get(route('issues.index'))
        ->assertOk()
        ->assertSee('Alpha task')
        ->assertSee('Beta task');
});

test('issues in kanban are ordered by due date ascending with undated tasks last', function () {
    $user = User::factory()->create();
    $project = Project::factory()->create(['user_id' => $user->id]);

    $this->seed(IssueDictionarySeeder::class);

    $backlog = IssueStatus::query()->where('key', 'backlog')->firstOrFail();
    $priority = IssuePriority::query()->where('key', 'medium')->firstOrFail();
    $category = IssueCategory::query()->where('name', 'Task')->firstOrFail();

    Issue::query()->create([
        'project_id' => $project->id,
        'client_id' => null,
        'client_contact_id' => null,
        'status_id' => $backlog->id,
        'priority_id' => $priority->id,
        'category_id' => $category->id,
        'author_id' => $user->id,
        'assignee_id' => null,
        'title' => 'Task today',
        'description' => null,
        'due_date' => now()->toDateString(),
        'completed_at' => null,
    ]);

    Issue::query()->create([
        'project_id' => $project->id,
        'client_id' => null,
        'client_contact_id' => null,
        'status_id' => $backlog->id,
        'priority_id' => $priority->id,
        'category_id' => $category->id,
        'author_id' => $user->id,
        'assignee_id' => null,
        'title' => 'Task tomorrow',
        'description' => null,
        'due_date' => now()->addDay()->toDateString(),
        'completed_at' => null,
    ]);

    Issue::query()->create([
        'project_id' => $project->id,
        'client_id' => null,
        'client_contact_id' => null,
        'status_id' => $backlog->id,
        'priority_id' => $priority->id,
        'category_id' => $category->id,
        'author_id' => $user->id,
        'assignee_id' => null,
        'title' => 'Task no due date',
        'description' => null,
        'due_date' => null,
        'completed_at' => null,
    ]);

    $this->actingAs($user)
        ->get(route('issues.index'))
        ->assertOk()
        ->assertSeeInOrder(['Task today', 'Task tomorrow', 'Task no due date']);
});
