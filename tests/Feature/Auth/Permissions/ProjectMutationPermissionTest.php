<?php

declare(strict_types=1);

use App\Enums\PermissionKey;
use App\Enums\RoleKey;
use App\Livewire\Projects\Form as ProjectForm;
use App\Livewire\Projects\Index as ProjectIndex;
use App\Models\Project;
use App\Models\User;
use Illuminate\Auth\Access\AuthorizationException;

// Project views use Flux UI. Direct component instantiation avoids view rendering
// while still exercising the full authorization + domain logic path.

beforeEach(function () {
    app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();
    (new \Database\Seeders\RolesAndPermissionsSeeder)->run();
});

// ─── Helper ──────────────────────────────────────────────────────────────────

function makeProject(int $userId): Project
{
    return Project::query()->create([
        'user_id' => $userId,
        'code' => 'TST-'.fake()->unique()->numberBetween(1000, 9999),
        'name' => 'Test Projekat',
        'is_active' => true,
    ]);
}

// ─── CANNOT: user without manage-projects ────────────────────────────────────

test('user without manage-projects cannot save project through form component', function () {
    $user = User::factory()->create();
    $this->actingAs($user);

    $initialCount = Project::query()->count();

    expect(fn () => (new ProjectForm)->save())
        ->toThrow(AuthorizationException::class);

    expect(Project::query()->count())->toBe($initialCount);
});

test('user without manage-projects cannot delete project', function () {
    $user = User::factory()->create();
    $this->actingAs($user);

    $project = makeProject($user->id);

    expect(fn () => (new ProjectIndex)->deleteProject($project->id))
        ->toThrow(AuthorizationException::class);

    expect(Project::find($project->id))->not()->toBeNull();
});

test('user without manage-projects cannot toggle project active state', function () {
    $user = User::factory()->create();
    $this->actingAs($user);

    $project = makeProject($user->id);
    $originalActive = $project->is_active;

    expect(fn () => (new ProjectIndex)->toggleActive($project->id))
        ->toThrow(AuthorizationException::class);

    expect(Project::find($project->id)?->is_active)->toBe($originalActive);
});

// ─── CAN: user with manage-projects ──────────────────────────────────────────

test('user with manage-projects can save project', function () {
    $user = User::factory()->create();
    $user->givePermissionTo(PermissionKey::ManageProjects->value);
    $this->actingAs($user);

    $code = 'NEW-'.fake()->unique()->numberBetween(1000, 9999);

    $component = new ProjectForm;
    $component->code = $code;
    $component->name = 'Novi Projekat';

    try {
        $component->save();
    } catch (\Throwable) {
        // redirectRoute may throw outside the Livewire lifecycle — project is already saved
    }

    expect(Project::query()->where('code', $code)->exists())->toBeTrue();
});

test('user with manage-projects can delete project', function () {
    $user = User::factory()->create();
    $user->givePermissionTo(PermissionKey::ManageProjects->value);
    $this->actingAs($user);

    $project = makeProject($user->id);

    (new ProjectIndex)->deleteProject($project->id);

    expect(Project::find($project->id))->toBeNull();
});

test('user with manage-projects can toggle project active state', function () {
    $user = User::factory()->create();
    $user->givePermissionTo(PermissionKey::ManageProjects->value);
    $this->actingAs($user);

    $project = makeProject($user->id);

    (new ProjectIndex)->toggleActive($project->id);

    expect(Project::find($project->id)?->is_active)->toBeFalse();
});

// ─── SUPER-ADMIN: Gate::before bypass ────────────────────────────────────────

test('super-admin can delete project via gate bypass', function () {
    $superAdmin = User::factory()->create();
    $superAdmin->assignRole(RoleKey::SuperAdmin->value);
    $this->actingAs($superAdmin);

    $project = makeProject($superAdmin->id);

    (new ProjectIndex)->deleteProject($project->id);

    expect(Project::find($project->id))->toBeNull();
});
