<?php

declare(strict_types=1);

use App\Enums\RoleKey;
use App\Livewire\Admin\Users\Index;
use App\Models\User;

beforeEach(function () {
    app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();
    (new \Database\Seeders\RolesAndPermissionsSeeder)->run();
});

// Helper: call component method without triggering Livewire view rendering.
// Guard logic in assignRole/revokeRole is pure DB + session — no Livewire lifecycle needed.
function callIndexMethod(string $method, mixed ...$args): void
{
    $component = new Index;
    $component->$method(...$args);
}

test('last super-admin cannot demote himself', function () {
    $superAdmin = User::factory()->create();
    $superAdmin->assignRole(RoleKey::SuperAdmin->value);

    callIndexMethod('assignRole', $superAdmin->id, RoleKey::User->value);

    expect($superAdmin->fresh()->hasRole(RoleKey::SuperAdmin->value))->toBeTrue();
    expect(User::role(RoleKey::SuperAdmin->value)->count())->toBe(1);
});

test('last super-admin cannot have super-admin role revoked', function () {
    $superAdmin = User::factory()->create();
    $superAdmin->assignRole(RoleKey::SuperAdmin->value);

    callIndexMethod('revokeRole', $superAdmin->id, RoleKey::SuperAdmin->value);

    expect($superAdmin->fresh()->hasRole(RoleKey::SuperAdmin->value))->toBeTrue();
    expect(User::role(RoleKey::SuperAdmin->value)->count())->toBe(1);
});

test('one super-admin cannot demote the only other super-admin if that would leave zero super-admins', function () {
    $actor = User::factory()->create();
    $actor->assignRole(RoleKey::User->value);

    $lastSuperAdmin = User::factory()->create();
    $lastSuperAdmin->assignRole(RoleKey::SuperAdmin->value);

    callIndexMethod('assignRole', $lastSuperAdmin->id, RoleKey::User->value);

    expect($lastSuperAdmin->fresh()->hasRole(RoleKey::SuperAdmin->value))->toBeTrue();
    expect(User::role(RoleKey::SuperAdmin->value)->count())->toBe(1);
});

test('super-admin can demote another super-admin when at least one super-admin remains', function () {
    $actor = User::factory()->create();
    $actor->assignRole(RoleKey::SuperAdmin->value);

    $target = User::factory()->create();
    $target->assignRole(RoleKey::SuperAdmin->value);

    callIndexMethod('assignRole', $target->id, RoleKey::User->value);

    expect($target->fresh()->hasRole(RoleKey::SuperAdmin->value))->toBeFalse();
    expect($target->fresh()->hasRole(RoleKey::User->value))->toBeTrue();
    expect(User::role(RoleKey::SuperAdmin->value)->count())->toBe(1);
});

test('assigning regular role to normal user still works', function () {
    $actor = User::factory()->create();
    $actor->assignRole(RoleKey::SuperAdmin->value);

    $regularUser = User::factory()->create();
    $regularUser->assignRole(RoleKey::User->value);

    callIndexMethod('assignRole', $regularUser->id, RoleKey::User->value);

    expect($regularUser->fresh()->hasRole(RoleKey::User->value))->toBeTrue();
    expect($regularUser->fresh()->hasRole(RoleKey::SuperAdmin->value))->toBeFalse();
    expect(User::role(RoleKey::SuperAdmin->value)->count())->toBe(1);
});

test('revoking non-super-admin role still works', function () {
    $actor = User::factory()->create();
    $actor->assignRole(RoleKey::SuperAdmin->value);

    $regularUser = User::factory()->create();
    $regularUser->assignRole(RoleKey::User->value);

    callIndexMethod('revokeRole', $regularUser->id, RoleKey::User->value);

    expect($regularUser->fresh()->hasRole(RoleKey::User->value))->toBeFalse();
    expect(User::role(RoleKey::SuperAdmin->value)->count())->toBe(1);
});
