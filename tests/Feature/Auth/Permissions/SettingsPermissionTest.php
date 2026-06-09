<?php

declare(strict_types=1);

use App\Enums\PermissionKey;
use App\Enums\RoleKey;
use App\Models\IssueCategory;
use App\Models\IssuePriority;
use App\Models\IssueStatus;
use App\Models\User;

// Settings routes are system configuration — only users with manage-settings permission may access them.
// The user role has manage-settings by default; tests use a user with no role to simulate access denial.
// Positive tests assert status != 403 only, since Flux view rendering is not under test here.

beforeEach(function () {
    app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();
    (new \Database\Seeders\RolesAndPermissionsSeeder)->run();
});

// ─── ISSUE STATUSES ──────────────────────────────────────────────────────────

test('user without manage-settings cannot access issue statuses index', function () {
    $user = User::factory()->create();

    $this->actingAsWithSession($user)
        ->get(route('settings.issue-statuses.index'))
        ->assertForbidden();
});

test('user without manage-settings cannot access issue statuses create', function () {
    $user = User::factory()->create();

    $this->actingAsWithSession($user)
        ->get(route('settings.issue-statuses.create'))
        ->assertForbidden();
});

test('user without manage-settings cannot access issue statuses edit', function () {
    $user = User::factory()->create();
    $status = IssueStatus::factory()->create();

    $this->actingAsWithSession($user)
        ->get(route('settings.issue-statuses.edit', $status))
        ->assertForbidden();
});

// ─── ISSUE PRIORITIES ────────────────────────────────────────────────────────

test('user without manage-settings cannot access issue priorities index', function () {
    $user = User::factory()->create();

    $this->actingAsWithSession($user)
        ->get(route('settings.issue-priorities.index'))
        ->assertForbidden();
});

test('user without manage-settings cannot access issue priorities create', function () {
    $user = User::factory()->create();

    $this->actingAsWithSession($user)
        ->get(route('settings.issue-priorities.create'))
        ->assertForbidden();
});

test('user without manage-settings cannot access issue priorities edit', function () {
    $user = User::factory()->create();
    $priority = IssuePriority::factory()->create();

    $this->actingAsWithSession($user)
        ->get(route('settings.issue-priorities.edit', $priority))
        ->assertForbidden();
});

// ─── ISSUE CATEGORIES ────────────────────────────────────────────────────────

test('user without manage-settings cannot access issue categories index', function () {
    $user = User::factory()->create();

    $this->actingAsWithSession($user)
        ->get(route('settings.issue-categories.index'))
        ->assertForbidden();
});

test('user without manage-settings cannot access issue categories create', function () {
    $user = User::factory()->create();

    $this->actingAsWithSession($user)
        ->get(route('settings.issue-categories.create'))
        ->assertForbidden();
});

test('user without manage-settings cannot access issue categories edit', function () {
    $user = User::factory()->create();
    $category = IssueCategory::factory()->create();

    $this->actingAsWithSession($user)
        ->get(route('settings.issue-categories.edit', $category))
        ->assertForbidden();
});

// ─── POSITIVE: user with manage-settings ─────────────────────────────────────

test('user with manage-settings can access issue statuses index', function () {
    $user = User::factory()->create();
    $user->givePermissionTo(PermissionKey::ManageSettings->value);

    $response = $this->actingAsWithSession($user)->get(route('settings.issue-statuses.index'));
    expect($response->getStatusCode())->not()->toBe(403);
});

test('user with manage-settings can access issue priorities index', function () {
    $user = User::factory()->create();
    $user->givePermissionTo(PermissionKey::ManageSettings->value);

    $response = $this->actingAsWithSession($user)->get(route('settings.issue-priorities.index'));
    expect($response->getStatusCode())->not()->toBe(403);
});

test('user with manage-settings can access issue categories index', function () {
    $user = User::factory()->create();
    $user->givePermissionTo(PermissionKey::ManageSettings->value);

    $response = $this->actingAsWithSession($user)->get(route('settings.issue-categories.index'));
    expect($response->getStatusCode())->not()->toBe(403);
});

// ─── POSITIVE: super-admin via Gate::before bypass ───────────────────────────

test('super-admin can access issue statuses index via gate bypass', function () {
    $superAdmin = User::factory()->create();
    $superAdmin->assignRole(RoleKey::SuperAdmin->value);

    $response = $this->actingAsWithSession($superAdmin)->get(route('settings.issue-statuses.index'));
    expect($response->getStatusCode())->not()->toBe(403);
});

test('super-admin can access issue priorities index via gate bypass', function () {
    $superAdmin = User::factory()->create();
    $superAdmin->assignRole(RoleKey::SuperAdmin->value);

    $response = $this->actingAsWithSession($superAdmin)->get(route('settings.issue-priorities.index'));
    expect($response->getStatusCode())->not()->toBe(403);
});

test('super-admin can access issue categories index via gate bypass', function () {
    $superAdmin = User::factory()->create();
    $superAdmin->assignRole(RoleKey::SuperAdmin->value);

    $response = $this->actingAsWithSession($superAdmin)->get(route('settings.issue-categories.index'));
    expect($response->getStatusCode())->not()->toBe(403);
});
