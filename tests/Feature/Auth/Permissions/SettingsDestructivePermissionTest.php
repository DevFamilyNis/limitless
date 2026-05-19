<?php

declare(strict_types=1);

use App\Enums\PermissionKey;
use App\Enums\RoleKey;
use App\Livewire\Settings\IssueCategories\Index as IssueCategoryIndex;
use App\Livewire\Settings\IssuePriorities\Index as IssuePriorityIndex;
use App\Livewire\Settings\IssueStatuses\Index as IssueStatusIndex;
use App\Models\IssueCategory;
use App\Models\IssuePriority;
use App\Models\IssueStatus;
use App\Models\User;
use Illuminate\Auth\Access\AuthorizationException;

// Settings views use Flux UI components which cannot be rendered in tests.
// Direct component instantiation is used to bypass view rendering —
// the guard logic (authorize + delete) has no Livewire lifecycle dependency.

beforeEach(function () {
    app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();
    (new \Database\Seeders\RolesAndPermissionsSeeder)->run();
});

// ─── CANNOT: user without manage-settings ────────────────────────────────────

test('user without manage-settings cannot delete issue status', function () {
    $user = User::factory()->create();
    $this->actingAs($user);

    $status = IssueStatus::factory()->create();

    expect(fn () => (new IssueStatusIndex)->deleteStatus($status->id))
        ->toThrow(AuthorizationException::class);

    expect(IssueStatus::find($status->id))->not()->toBeNull();
});

test('user without manage-settings cannot delete issue priority', function () {
    $user = User::factory()->create();
    $this->actingAs($user);

    $priority = IssuePriority::factory()->create();

    expect(fn () => (new IssuePriorityIndex)->deletePriority($priority->id))
        ->toThrow(AuthorizationException::class);

    expect(IssuePriority::find($priority->id))->not()->toBeNull();
});

test('user without manage-settings cannot delete issue category', function () {
    $user = User::factory()->create();
    $this->actingAs($user);

    $category = IssueCategory::factory()->create();

    expect(fn () => (new IssueCategoryIndex)->deleteCategory($category->id))
        ->toThrow(AuthorizationException::class);

    expect(IssueCategory::find($category->id))->not()->toBeNull();
});

// ─── CAN: user with manage-settings ──────────────────────────────────────────

test('user with manage-settings can delete issue status', function () {
    $user = User::factory()->create();
    $user->givePermissionTo(PermissionKey::ManageSettings->value);
    $this->actingAs($user);

    $status = IssueStatus::factory()->create();

    (new IssueStatusIndex)->deleteStatus($status->id);

    expect(IssueStatus::find($status->id))->toBeNull();
});

test('user with manage-settings can delete issue priority', function () {
    $user = User::factory()->create();
    $user->givePermissionTo(PermissionKey::ManageSettings->value);
    $this->actingAs($user);

    $priority = IssuePriority::factory()->create();

    (new IssuePriorityIndex)->deletePriority($priority->id);

    expect(IssuePriority::find($priority->id))->toBeNull();
});

test('user with manage-settings can delete issue category', function () {
    $user = User::factory()->create();
    $user->givePermissionTo(PermissionKey::ManageSettings->value);
    $this->actingAs($user);

    $category = IssueCategory::factory()->create();

    (new IssueCategoryIndex)->deleteCategory($category->id);

    expect(IssueCategory::find($category->id))->toBeNull();
});

// ─── CAN: super-admin via Gate::before bypass ────────────────────────────────

test('super-admin can delete issue status via gate bypass', function () {
    $superAdmin = User::factory()->create();
    $superAdmin->assignRole(RoleKey::SuperAdmin->value);
    $this->actingAs($superAdmin);

    $status = IssueStatus::factory()->create();

    (new IssueStatusIndex)->deleteStatus($status->id);

    expect(IssueStatus::find($status->id))->toBeNull();
});
