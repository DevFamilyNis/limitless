<?php

use App\Enums\PermissionKey;
use App\Enums\RoleKey;
use App\Models\User;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

beforeEach(function () {
    app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();
    (new \Database\Seeders\RolesAndPermissionsSeeder)->run();
});

test('super-admin bypasses all permission checks', function () {
    $superAdmin = User::factory()->create();
    $superAdmin->assignRole(RoleKey::SuperAdmin->value);

    expect($superAdmin->can(PermissionKey::ManageUsers->value))->toBeTrue();
    expect($superAdmin->can(PermissionKey::ManageRoles->value))->toBeTrue();
    expect($superAdmin->can(PermissionKey::ManageInvoices->value))->toBeTrue();
    expect($superAdmin->can(PermissionKey::ManageKpo->value))->toBeTrue();
});

test('regular user has no manage-users permission by default', function () {
    $user = User::factory()->create();
    $user->assignRole(RoleKey::User->value);

    expect($user->can(PermissionKey::ManageUsers->value))->toBeFalse();
    expect($user->can(PermissionKey::ManageRoles->value))->toBeFalse();
});

test('regular user can view and manage standard resources', function () {
    $user = User::factory()->create();
    $user->assignRole(RoleKey::User->value);

    expect($user->can(PermissionKey::ViewDashboard->value))->toBeTrue();
    expect($user->can(PermissionKey::ManageClients->value))->toBeTrue();
    expect($user->can(PermissionKey::ManageInvoices->value))->toBeTrue();
    expect($user->can(PermissionKey::ManageLeads->value))->toBeTrue();
    expect($user->can(PermissionKey::ManageIssues->value))->toBeTrue();
});

test('regular user cannot manage kpo', function () {
    $user = User::factory()->create();
    $user->assignRole(RoleKey::User->value);

    expect($user->can(PermissionKey::ManageKpo->value))->toBeFalse();
});

test('admin users index route is inaccessible without manage-users permission', function () {
    $user = User::factory()->create();
    $user->assignRole(RoleKey::User->value);

    $this->actingAsWithSession($user)
        ->get(route('admin.users.index'))
        ->assertForbidden();
});

test('super-admin can access admin users index route without being forbidden', function () {
    $superAdmin = User::factory()->create();
    $superAdmin->assignRole(RoleKey::SuperAdmin->value);

    // Proveravamo da super-admin NIJE blokiran (ne dobija 403) — view rendering test je odvojeno
    $response = $this->actingAsWithSession($superAdmin)->get(route('admin.users.index'));
    expect($response->getStatusCode())->not()->toBe(403);
});

test('user without any role cannot access admin users route', function () {
    $user = User::factory()->create();

    $this->actingAsWithSession($user)
        ->get(route('admin.users.index'))
        ->assertForbidden();
});

test('super-admin role has all permissions', function () {
    $superAdminRole = Role::query()->where('name', RoleKey::SuperAdmin->value)->first();

    // super-admin role treba da ima sve permisije
    if ($superAdminRole) {
        $allPermissions = collect(PermissionKey::cases())->map(fn ($p) => $p->value);
        $rolePermissions = $superAdminRole->permissions->pluck('name');

        foreach ($allPermissions as $permission) {
            expect($rolePermissions->contains($permission))->toBeTrue(
                "super-admin role missing permission: {$permission}"
            );
        }
    } else {
        // Rola ne postoji u test DB — Gate::before garantuje bypass bez eksplicitnih permisija
        $superAdmin = User::factory()->create();
        $superAdmin->assignRole(RoleKey::SuperAdmin->value);
        expect($superAdmin->can(PermissionKey::ManageUsers->value))->toBeTrue();
    }
});

test('user can be assigned super-admin role', function () {
    $user = User::factory()->create();
    $user->assignRole(RoleKey::SuperAdmin->value);

    expect($user->hasRole(RoleKey::SuperAdmin->value))->toBeTrue();
    expect($user->can(PermissionKey::ManageUsers->value))->toBeTrue();
});

test('roles seeder creates expected roles', function () {
    (new \Database\Seeders\RolesAndPermissionsSeeder)->run();

    expect(Role::query()->where('name', RoleKey::SuperAdmin->value)->exists())->toBeTrue();
    expect(Role::query()->where('name', RoleKey::User->value)->exists())->toBeTrue();
    expect(Permission::query()->where('name', PermissionKey::ManageUsers->value)->exists())->toBeTrue();
    expect(Permission::query()->where('name', PermissionKey::ManageInvoices->value)->exists())->toBeTrue();
});
