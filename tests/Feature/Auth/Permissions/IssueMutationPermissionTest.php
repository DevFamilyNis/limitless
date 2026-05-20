<?php

declare(strict_types=1);

use App\Enums\PermissionKey;
use App\Enums\RoleKey;
use App\Livewire\Issues\Form as IssueForm;
use App\Livewire\Issues\Index as IssueIndex;
use App\Livewire\Issues\Show as IssueShow;
use App\Models\Issue;
use App\Models\IssueCategory;
use App\Models\IssueComment;
use App\Models\IssuePriority;
use App\Models\IssueStatus;
use App\Models\Project;
use App\Models\User;
use Database\Seeders\IssueDictionarySeeder;
use Illuminate\Auth\Access\AuthorizationException;

// Issue views use Flux UI. Direct component instantiation avoids view rendering.
// Board::moveIssue() is guarded identically to Index::moveIssue() — tested via Index.
// Show component is set up with a real issue before calling mutation methods.

beforeEach(function () {
    app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();
    (new \Database\Seeders\RolesAndPermissionsSeeder)->run();
    $this->seed(IssueDictionarySeeder::class);
});

// ─── Helpers ─────────────────────────────────────────────────────────────────

function makeIssue(int $userId): Issue
{
    $project = Project::factory()->create(['user_id' => $userId]);
    $status = IssueStatus::query()->where('key', 'backlog')->first();
    $priority = IssuePriority::query()->where('key', 'medium')->first();
    $category = IssueCategory::query()->where('name', 'Task')->first();

    return Issue::query()->create([
        'project_id' => $project->id,
        'status_id' => $status->id,
        'priority_id' => $priority->id,
        'category_id' => $category->id,
        'author_id' => $userId,
        'title' => 'Test Issue',
    ]);
}

function makeShowComponent(Issue $issue): IssueShow
{
    $component = new IssueShow;
    $component->issue = $issue;

    return $component;
}

// ─── CANNOT: user without manage-issues ──────────────────────────────────────

test('user without manage-issues cannot save issue', function () {
    $user = User::factory()->create();
    $this->actingAs($user);

    $initialCount = Issue::query()->count();

    expect(fn () => (new IssueForm)->save())
        ->toThrow(AuthorizationException::class);

    expect(Issue::query()->count())->toBe($initialCount);
});

test('user without manage-issues cannot move issue status', function () {
    $user = User::factory()->create();
    $this->actingAs($user);

    $issue = makeIssue($user->id);
    $done = IssueStatus::query()->where('key', 'done')->first();
    $originalStatusId = $issue->status_id;

    expect(fn () => (new IssueIndex)->moveIssue($issue->id, $done->id))
        ->toThrow(AuthorizationException::class);

    expect(Issue::find($issue->id)?->status_id)->toBe($originalStatusId);
});

test('user without manage-issues cannot add comment', function () {
    $user = User::factory()->create();
    $this->actingAs($user);

    $issue = makeIssue($user->id);
    $component = makeShowComponent($issue);

    expect(fn () => $component->addComment())
        ->toThrow(AuthorizationException::class);

    expect(IssueComment::query()->where('issue_id', $issue->id)->count())->toBe(0);
});

test('user without manage-issues cannot delete comment', function () {
    $user = User::factory()->create();
    $this->actingAs($user);

    $issue = makeIssue($user->id);
    $comment = IssueComment::query()->create([
        'issue_id' => $issue->id,
        'author_id' => $user->id,
        'body' => 'Postojeći komentar',
    ]);

    $component = makeShowComponent($issue);

    expect(fn () => $component->deleteComment($comment->id))
        ->toThrow(AuthorizationException::class);

    expect(IssueComment::find($comment->id))->not()->toBeNull();
});

test('user without manage-issues cannot upload attachments', function () {
    $user = User::factory()->create();
    $this->actingAs($user);

    $issue = makeIssue($user->id);
    $component = makeShowComponent($issue);

    expect(fn () => $component->uploadAttachments())
        ->toThrow(AuthorizationException::class);
});

test('user without manage-issues cannot delete attachment', function () {
    $user = User::factory()->create();
    $this->actingAs($user);

    $issue = makeIssue($user->id);
    $component = makeShowComponent($issue);

    expect(fn () => $component->deleteAttachment(999))
        ->toThrow(AuthorizationException::class);
});

// ─── CAN: user with manage-issues ────────────────────────────────────────────

test('user with manage-issues can save issue', function () {
    $user = User::factory()->create();
    $user->givePermissionTo(PermissionKey::ManageIssues->value);
    $this->actingAs($user);

    $project = Project::factory()->create(['user_id' => $user->id]);
    $status = IssueStatus::query()->where('key', 'backlog')->first();
    $priority = IssuePriority::query()->where('key', 'medium')->first();
    $category = IssueCategory::query()->where('name', 'Task')->first();

    $component = new IssueForm;
    $component->projectId = (string) $project->id;
    $component->statusId = (string) $status->id;
    $component->priorityId = (string) $priority->id;
    $component->categoryId = (string) $category->id;
    $component->title = 'Novi Issue';

    try {
        $component->save();
    } catch (\Throwable) {
        // redirectRoute may throw outside the Livewire lifecycle — issue is already saved
    }

    expect(Issue::query()->where('title', 'Novi Issue')->exists())->toBeTrue();
});

test('user with manage-issues can move issue status', function () {
    $user = User::factory()->create();
    $user->givePermissionTo(PermissionKey::ManageIssues->value);
    $this->actingAs($user);

    $issue = makeIssue($user->id);
    $done = IssueStatus::query()->where('key', 'done')->first();

    (new IssueIndex)->moveIssue($issue->id, $done->id);

    expect(Issue::find($issue->id)?->status_id)->toBe($done->id);
});

test('user with manage-issues can add comment', function () {
    $user = User::factory()->create();
    $user->givePermissionTo(PermissionKey::ManageIssues->value);
    $this->actingAs($user);

    $issue = makeIssue($user->id);
    $component = makeShowComponent($issue);
    $component->comment = 'Dozvoljeni komentar';

    $component->addComment();

    expect(IssueComment::query()->where('issue_id', $issue->id)->exists())->toBeTrue();
});

// ─── SUPER-ADMIN: Gate::before bypass ────────────────────────────────────────

test('super-admin can move issue status via gate bypass', function () {
    $superAdmin = User::factory()->create();
    $superAdmin->assignRole(RoleKey::SuperAdmin->value);
    $this->actingAs($superAdmin);

    $issue = makeIssue($superAdmin->id);
    $done = IssueStatus::query()->where('key', 'done')->first();

    (new IssueIndex)->moveIssue($issue->id, $done->id);

    expect(Issue::find($issue->id)?->status_id)->toBe($done->id);
});
