<?php

declare(strict_types=1);

use App\Enums\PermissionKey;
use App\Enums\RoleKey;
use App\Livewire\Admin\Users\Index;
use App\Models\User;
use Illuminate\Auth\Access\AuthorizationException;

// Admin/Users/Index views use Flux UI. Direct component instantiation avoids view rendering.
// Auth context is set via $this->actingAs() before calling component methods.

beforeEach(function () {
    app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();
    (new \Database\Seeders\RolesAndPermissionsSeeder)->run();
});

// ─── Permission guard: cannot ─────────────────────────────────────────────────

test('user without manage-users cannot assign role', function () {
    $actor = User::factory()->create(); // no role = no manage-users
    $this->actingAs($actor);

    $target = User::factory()->create();
    $target->assignRole(RoleKey::User->value);

    expect(fn () => (new Index)->assignRole($target->id, RoleKey::SuperAdmin->value))
        ->toThrow(AuthorizationException::class);

    expect($target->fresh()->hasRole(RoleKey::SuperAdmin->value))->toBeFalse();
    expect($target->fresh()->hasRole(RoleKey::User->value))->toBeTrue();
});

test('user without manage-users cannot revoke role', function () {
    $actor = User::factory()->create(); // no role = no manage-users
    $this->actingAs($actor);

    $target = User::factory()->create();
    $target->assignRole(RoleKey::User->value);

    expect(fn () => (new Index)->revokeRole($target->id, RoleKey::User->value))
        ->toThrow(AuthorizationException::class);

    expect($target->fresh()->hasRole(RoleKey::User->value))->toBeTrue();
});

// ─── Permission guard: can ────────────────────────────────────────────────────

test('user with manage-users can assign role', function () {
    $actor = User::factory()->create();
    $actor->givePermissionTo(PermissionKey::ManageUsers->value);
    $this->actingAs($actor);

    $target = User::factory()->create();

    (new Index)->assignRole($target->id, RoleKey::User->value);

    expect($target->fresh()->hasRole(RoleKey::User->value))->toBeTrue();
});

test('user with manage-users can revoke role', function () {
    $actor = User::factory()->create();
    $actor->givePermissionTo(PermissionKey::ManageUsers->value);
    $this->actingAs($actor);

    // Need a second super-admin so revoke doesn't hit the lockout guard
    $otherSuperAdmin = User::factory()->create();
    $otherSuperAdmin->assignRole(RoleKey::SuperAdmin->value);

    $target = User::factory()->create();
    $target->assignRole(RoleKey::SuperAdmin->value);

    (new Index)->revokeRole($target->id, RoleKey::SuperAdmin->value);

    expect($target->fresh()->hasRole(RoleKey::SuperAdmin->value))->toBeFalse();
});

// ─── Super-admin lockout protection ──────────────────────────────────────────
// authorize() passes for super-admins (Gate::before), lockout logic runs after.

test('last super-admin cannot demote himself', function () {
    $superAdmin = User::factory()->create();
    $superAdmin->assignRole(RoleKey::SuperAdmin->value);
    $this->actingAs($superAdmin); // super-admin bypasses authorize via Gate::before

    (new Index)->assignRole($superAdmin->id, RoleKey::User->value);

    expect($superAdmin->fresh()->hasRole(RoleKey::SuperAdmin->value))->toBeTrue();
    expect(User::role(RoleKey::SuperAdmin->value)->count())->toBe(1);
});

test('last super-admin cannot have super-admin role revoked', function () {
    $superAdmin = User::factory()->create();
    $superAdmin->assignRole(RoleKey::SuperAdmin->value);
    $this->actingAs($superAdmin);

    (new Index)->revokeRole($superAdmin->id, RoleKey::SuperAdmin->value);

    expect($superAdmin->fresh()->hasRole(RoleKey::SuperAdmin->value))->toBeTrue();
    expect(User::role(RoleKey::SuperAdmin->value)->count())->toBe(1);
});

test('one super-admin cannot demote the only other super-admin if that would leave zero super-admins', function () {
    $actor = User::factory()->create();
    $actor->givePermissionTo(PermissionKey::ManageUsers->value); // non-super-admin with manage-users
    $this->actingAs($actor);

    $lastSuperAdmin = User::factory()->create();
    $lastSuperAdmin->assignRole(RoleKey::SuperAdmin->value);

    (new Index)->assignRole($lastSuperAdmin->id, RoleKey::User->value);

    expect($lastSuperAdmin->fresh()->hasRole(RoleKey::SuperAdmin->value))->toBeTrue();
    expect(User::role(RoleKey::SuperAdmin->value)->count())->toBe(1);
});

test('super-admin can demote another super-admin when at least one super-admin remains', function () {
    $actor = User::factory()->create();
    $actor->assignRole(RoleKey::SuperAdmin->value);
    $this->actingAs($actor);

    $target = User::factory()->create();
    $target->assignRole(RoleKey::SuperAdmin->value);

    (new Index)->assignRole($target->id, RoleKey::User->value);

    expect($target->fresh()->hasRole(RoleKey::SuperAdmin->value))->toBeFalse();
    expect($target->fresh()->hasRole(RoleKey::User->value))->toBeTrue();
    expect(User::role(RoleKey::SuperAdmin->value)->count())->toBe(1);
});

test('assigning regular role to normal user still works', function () {
    $actor = User::factory()->create();
    $actor->assignRole(RoleKey::SuperAdmin->value);
    $this->actingAs($actor);

    $regularUser = User::factory()->create();
    $regularUser->assignRole(RoleKey::User->value);

    (new Index)->assignRole($regularUser->id, RoleKey::User->value);

    expect($regularUser->fresh()->hasRole(RoleKey::User->value))->toBeTrue();
    expect($regularUser->fresh()->hasRole(RoleKey::SuperAdmin->value))->toBeFalse();
    expect(User::role(RoleKey::SuperAdmin->value)->count())->toBe(1);
});

test('revoking non-super-admin role still works', function () {
    $actor = User::factory()->create();
    $actor->assignRole(RoleKey::SuperAdmin->value);
    $this->actingAs($actor);

    $regularUser = User::factory()->create();
    $regularUser->assignRole(RoleKey::User->value);

    (new Index)->revokeRole($regularUser->id, RoleKey::User->value);

    expect($regularUser->fresh()->hasRole(RoleKey::User->value))->toBeFalse();
    expect(User::role(RoleKey::SuperAdmin->value)->count())->toBeGreaterThanOrEqual(1);
});
