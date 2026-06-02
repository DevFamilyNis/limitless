<?php

declare(strict_types=1);

use App\Enums\PermissionKey;
use App\Enums\RoleKey;
use App\Livewire\Settings\IssueCategories\Form as IssueCategoryForm;
use App\Livewire\Settings\IssuePriorities\Form as IssuePriorityForm;
use App\Livewire\Settings\IssueStatuses\Form as IssueStatusForm;
use App\Models\IssueCategory;
use App\Models\IssuePriority;
use App\Models\IssueStatus;
use App\Models\User;
use Illuminate\Auth\Access\AuthorizationException;

// Settings Form views use Flux UI. Direct component instantiation avoids view rendering.
// Complements SettingsDestructivePermissionTest which covers the delete methods.

beforeEach(function () {
    app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();
    (new \Database\Seeders\RolesAndPermissionsSeeder)->run();
});

// ─── CANNOT: user without manage-settings ────────────────────────────────────

test('user without manage-settings cannot save issue status', function () {
    $user = User::factory()->create();
    $this->actingAs($user);

    $initialCount = IssueStatus::query()->count();

    expect(fn () => (new IssueStatusForm)->save())
        ->toThrow(AuthorizationException::class);

    expect(IssueStatus::query()->count())->toBe($initialCount);
});

test('user without manage-settings cannot save issue priority', function () {
    $user = User::factory()->create();
    $this->actingAs($user);

    $initialCount = IssuePriority::query()->count();

    expect(fn () => (new IssuePriorityForm)->save())
        ->toThrow(AuthorizationException::class);

    expect(IssuePriority::query()->count())->toBe($initialCount);
});

test('user without manage-settings cannot save issue category', function () {
    $user = User::factory()->create();
    $this->actingAs($user);

    $initialCount = IssueCategory::query()->count();

    expect(fn () => (new IssueCategoryForm)->save())
        ->toThrow(AuthorizationException::class);

    expect(IssueCategory::query()->count())->toBe($initialCount);
});

// ─── CAN: user with manage-settings ──────────────────────────────────────────

test('user with manage-settings can save issue status', function () {
    $user = User::factory()->create();
    $user->givePermissionTo(PermissionKey::ManageSettings->value);
    $this->actingAs($user);

    $component = new IssueStatusForm;
    $component->key = 'review';
    $component->name = 'In Review';
    $component->sortOrder = '50';
    $component->isActive = true;

    try {
        $component->save();
    } catch (\Throwable) {
        // redirectRoute may throw outside the Livewire lifecycle — status is already saved
    }

    expect(IssueStatus::query()->where('key', 'review')->exists())->toBeTrue();
});

test('user with manage-settings can save issue priority', function () {
    $user = User::factory()->create();
    $user->givePermissionTo(PermissionKey::ManageSettings->value);
    $this->actingAs($user);

    $component = new IssuePriorityForm;
    $component->key = 'critical';
    $component->name = 'Critical';
    $component->sortOrder = '50';

    try {
        $component->save();
    } catch (\Throwable) {
        // redirectRoute may throw outside the Livewire lifecycle — priority is already saved
    }

    expect(IssuePriority::query()->where('key', 'critical')->exists())->toBeTrue();
});

test('user with manage-settings can save issue category', function () {
    $user = User::factory()->create();
    $user->givePermissionTo(PermissionKey::ManageSettings->value);
    $this->actingAs($user);

    $component = new IssueCategoryForm;
    $component->name = 'Improvement';
    $component->isActive = true;

    try {
        $component->save();
    } catch (\Throwable) {
        // redirectRoute may throw outside the Livewire lifecycle — category is already saved
    }

    expect(IssueCategory::query()->where('name', 'Improvement')->exists())->toBeTrue();
});

// ─── SUPER-ADMIN: Gate::before bypass ────────────────────────────────────────

test('super-admin can save issue status via gate bypass', function () {
    $superAdmin = User::factory()->create();
    $superAdmin->assignRole(RoleKey::SuperAdmin->value);
    $this->actingAs($superAdmin);

    $component = new IssueStatusForm;
    $component->key = 'archived';
    $component->name = 'Archived';
    $component->sortOrder = '99';
    $component->isActive = false;

    try {
        $component->save();
    } catch (\Throwable) {
        // redirectRoute may throw outside the Livewire lifecycle — status is already saved
    }

    expect(IssueStatus::query()->where('key', 'archived')->exists())->toBeTrue();
});
