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

test('user can create project', function () {
    $user = User::factory()->create();

    Livewire::actingAs($user)->test(Form::class)
        ->set('code', 'EMPAY')
        ->set('name', 'Empay Projekat')
        ->set('description', 'Test opis')
        ->call('save')
        ->assertRedirect(route('projects.index', absolute: false));

    $this->assertDatabaseHas('projects', [
        'user_id' => $user->id,
        'code' => 'EMPAY',
        'name' => 'Empay Projekat',
        'is_active' => true,
    ]);
});

test('user can search projects', function () {
    $user = User::factory()->create();

    Project::query()->create([
        'user_id' => $user->id,
        'code' => 'EMPAY',
        'name' => 'Empay Projekat',
        'is_active' => true,
    ]);

    Project::query()->create([
        'user_id' => $user->id,
        'code' => 'FM',
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

    $project = Project::query()->create([
        'user_id' => $user->id,
        'code' => 'EMPAY',
        'name' => 'Stari naziv',
        'is_active' => true,
    ]);

    Livewire::actingAs($user)->test(Form::class, ['project' => $project])
        ->set('name', 'Novi naziv')
        ->set('description', 'Novi opis')
        ->call('save')
        ->assertRedirect(route('projects.index', absolute: false));

    $this->assertDatabaseHas('projects', [
        'id' => $project->id,
        'name' => 'Novi naziv',
        'description' => 'Novi opis',
    ]);
});

test('user can deactivate and activate project', function () {
    $user = User::factory()->create();

    $project = Project::query()->create([
        'user_id' => $user->id,
        'code' => 'EMPAY',
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

    $project = Project::query()->create([
        'user_id' => $user->id,
        'code' => 'EMPAY',
        'name' => 'Empay Projekat',
        'is_active' => true,
    ]);

    Livewire::actingAs($user)->test(Index::class)
        ->call('deleteProject', $project->id);

    $this->assertDatabaseMissing('projects', [
        'id' => $project->id,
    ]);
});
