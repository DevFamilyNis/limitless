<?php

use App\Livewire\Projects\Form;
use App\Livewire\Projects\Index;
use App\Models\Project;
use App\Models\User;
use Livewire\Livewire;

test('projects page is displayed', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->get(route('projects.index'))
        ->assertOk();
});

test('create project page is displayed', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->get(route('projects.create'))
        ->assertOk()
        ->assertSee('Novi projekat');
});

test('project show page is displayed for owner', function () {
    $user = User::factory()->create();
    $projectCode = 'EMPAY-'.fake()->unique()->numberBetween(1000, 9999);

    $project = Project::query()->create([
        'user_id' => $user->id,
        'code' => $projectCode,
        'name' => 'Empay Projekat',
        'is_active' => true,
    ]);

    $this->actingAs($user)
        ->get(route('projects.show', $project))
        ->assertOk()
        ->assertSee('Detalji projekta');
});

test('project show page is displayed for another user in shared workspace', function () {
    $owner = User::factory()->create();
    $anotherUser = User::factory()->create();
    $projectCode = 'EMPAY-'.fake()->unique()->numberBetween(1000, 9999);

    $project = Project::query()->create([
        'user_id' => $owner->id,
        'code' => $projectCode,
        'name' => 'Privatan projekat',
        'is_active' => true,
    ]);

    $this->actingAs($anotherUser)
        ->get(route('projects.show', $project))
        ->assertOk()
        ->assertSee('Privatan projekat');
});

test('user can create project', function () {
    $user = User::factory()->create();
    $projectCode = 'EMPAY-'.fake()->unique()->numberBetween(1000, 9999);

    Livewire::actingAs($user)->test(Form::class)
        ->set('code', $projectCode)
        ->set('name', 'Empay Projekat')
        ->set('projectColor', 'slate')
        ->set('description', 'Test opis')
        ->call('save')
        ->assertRedirect(route('projects.index', absolute: false));

    $this->assertDatabaseHas('projects', [
        'user_id' => $user->id,
        'code' => $projectCode,
        'name' => 'Empay Projekat',
        'project_color' => 'slate',
        'is_active' => true,
    ]);
});

test('user can search projects', function () {
    $user = User::factory()->create();
    $empayCode = 'EMPAY-'.fake()->unique()->numberBetween(1000, 9999);
    $fmCode = 'FM-'.fake()->unique()->numberBetween(1000, 9999);

    Project::query()->create([
        'user_id' => $user->id,
        'code' => $empayCode,
        'name' => 'Empay Projekat',
        'is_active' => true,
    ]);

    Project::query()->create([
        'user_id' => $user->id,
        'code' => $fmCode,
        'name' => 'Facility Management',
        'is_active' => true,
    ]);

    Livewire::actingAs($user)->test(Index::class)
        ->set('search', 'EMPAY')
        ->assertSee('Empay Projekat')
        ->assertDontSee('Facility Management');
});

test('user can update project', function () {
    $user = User::factory()->create();
    $projectCode = 'EMPAY-'.fake()->unique()->numberBetween(1000, 9999);

    $project = Project::query()->create([
        'user_id' => $user->id,
        'code' => $projectCode,
        'name' => 'Stari naziv',
        'is_active' => true,
    ]);

    Livewire::actingAs($user)->test(Form::class, ['project' => $project])
        ->set('name', 'Novi naziv')
        ->set('projectColor', 'teal')
        ->set('description', 'Novi opis')
        ->call('save')
        ->assertRedirect(route('projects.index', absolute: false));

    $this->assertDatabaseHas('projects', [
        'id' => $project->id,
        'name' => 'Novi naziv',
        'project_color' => 'teal',
        'description' => 'Novi opis',
    ]);
});

test('user can deactivate and activate project', function () {
    $user = User::factory()->create();
    $projectCode = 'EMPAY-'.fake()->unique()->numberBetween(1000, 9999);

    $project = Project::query()->create([
        'user_id' => $user->id,
        'code' => $projectCode,
        'name' => 'Empay Projekat',
        'is_active' => true,
    ]);

    Livewire::actingAs($user)->test(Index::class)
        ->call('toggleActive', $project->id);

    $this->assertDatabaseHas('projects', [
        'id' => $project->id,
        'is_active' => false,
    ]);

    Livewire::actingAs($user)->test(Index::class)
        ->call('toggleActive', $project->id);

    $this->assertDatabaseHas('projects', [
        'id' => $project->id,
        'is_active' => true,
    ]);
});

test('user can delete project', function () {
    $user = User::factory()->create();
    $projectCode = 'EMPAY-'.fake()->unique()->numberBetween(1000, 9999);

    $project = Project::query()->create([
        'user_id' => $user->id,
        'code' => $projectCode,
        'name' => 'Empay Projekat',
        'is_active' => true,
    ]);

    Livewire::actingAs($user)->test(Index::class)
        ->call('deleteProject', $project->id);

    $this->assertDatabaseMissing('projects', [
        'id' => $project->id,
    ]);
});
