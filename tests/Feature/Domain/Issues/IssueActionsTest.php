<?php

use App\Domain\Issues\Actions\AddIssueCommentAction;
use App\Domain\Issues\Actions\MoveIssueAction;
use App\Domain\Issues\Actions\UpsertIssueAction;
use App\Domain\Issues\DTO\AddIssueCommentData;
use App\Domain\Issues\DTO\MoveIssueData;
use App\Domain\Issues\DTO\UpsertIssueData;
use App\Models\Issue;
use App\Models\IssueCategory;
use App\Models\IssueComment;
use App\Models\IssuePriority;
use App\Models\IssueStatus;
use App\Models\Project;
use App\Models\User;
use Database\Seeders\IssueDictionarySeeder;

test('upsert issue action sets author id and manages completed at from status', function () {
    $this->seed(IssueDictionarySeeder::class);

    $user = User::factory()->create();
    $this->actingAs($user);
    $project = Project::factory()->create(['user_id' => $user->id]);
    $done = IssueStatus::query()->where('key', 'done')->firstOrFail();
    $backlog = IssueStatus::query()->where('key', 'backlog')->firstOrFail();
    $priority = IssuePriority::query()->where('key', 'medium')->firstOrFail();
    $category = IssueCategory::query()->where('name', 'Task')->firstOrFail();

    $issue = app(UpsertIssueAction::class)->execute(
        UpsertIssueData::fromArray([
            'user_id' => $user->id,
            'project_id' => $project->id,
            'status_id' => $done->id,
            'priority_id' => $priority->id,
            'category_id' => $category->id,
            'title' => 'Domain issue',
        ])
    );

    expect($issue->author_id)->toBe($user->id);
    expect($issue->completed_at)->not()->toBeNull();

    $updated = app(UpsertIssueAction::class)->execute(
        UpsertIssueData::fromArray([
            'user_id' => $user->id,
            'issue_id' => $issue->id,
            'project_id' => $project->id,
            'status_id' => $backlog->id,
            'priority_id' => $priority->id,
            'category_id' => $category->id,
            'title' => 'Domain issue updated',
        ])
    );

    expect($updated->completed_at)->toBeNull();
});

test('move issue action toggles completed at based on destination status', function () {
    $this->seed(IssueDictionarySeeder::class);

    $user = User::factory()->create();
    $this->actingAs($user);
    $project = Project::factory()->create(['user_id' => $user->id]);
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
        'title' => 'Move me',
        'description' => null,
        'due_date' => null,
        'completed_at' => null,
    ]);

    app(MoveIssueAction::class)->execute(
        MoveIssueData::fromArray([
            'user_id' => $user->id,
            'issue_id' => $issue->id,
            'to_status_id' => $done->id,
        ])
    );

    $issue->refresh();
    expect($issue->completed_at)->not()->toBeNull();

    app(MoveIssueAction::class)->execute(
        MoveIssueData::fromArray([
            'user_id' => $user->id,
            'issue_id' => $issue->id,
            'to_status_id' => $backlog->id,
        ])
    );

    $issue->refresh();
    expect($issue->completed_at)->toBeNull();
});

test('add issue comment action sets comment author from authenticated user', function () {
    $this->seed(IssueDictionarySeeder::class);

    $author = User::factory()->create();
    $anotherUser = User::factory()->create();
    $this->actingAs($author);

    $project = Project::factory()->create(['user_id' => $author->id]);
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
        'author_id' => $author->id,
        'assignee_id' => null,
        'title' => 'Issue with comments',
        'description' => null,
        'due_date' => null,
        'completed_at' => null,
    ]);

    $comment = app(AddIssueCommentAction::class)->execute(
        AddIssueCommentData::fromArray([
            'user_id' => $anotherUser->id,
            'issue_id' => $issue->id,
            'body' => 'Domain comment',
        ])
    );

    expect($comment)->toBeInstanceOf(IssueComment::class);
    expect($comment->author_id)->toBe($author->id);
});
