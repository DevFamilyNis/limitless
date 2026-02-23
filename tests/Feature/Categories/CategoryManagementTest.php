<?php

use App\Livewire\Categories\Form;
use App\Livewire\Categories\Index;
use App\Models\Category;
use App\Models\CategoryType;
use App\Models\User;
use Livewire\Livewire;

test('categories page is displayed', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->get(route('categories.index'))
        ->assertOk();
});

test('create category page is displayed', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->get(route('categories.create'))
        ->assertOk()
        ->assertSee('Nova kategorija');
});

test('user can create category', function () {
    $user = User::factory()->create();
    $expenseTypeId = CategoryType::query()->where('key', 'expense')->value('id');

    Livewire::actingAs($user)->test(Form::class)
        ->set('categoryTypeId', (string) $expenseTypeId)
        ->set('name', 'Gorivo')
        ->call('save')
        ->assertRedirect(route('categories.index', absolute: false));

    $this->assertDatabaseHas('categories', [
        'user_id' => $user->id,
        'category_type_id' => $expenseTypeId,
        'name' => 'Gorivo',
    ]);
});

test('user can filter categories by type', function () {
    $user = User::factory()->create();
    $incomeTypeId = CategoryType::query()->where('key', 'income')->value('id');
    $expenseTypeId = CategoryType::query()->where('key', 'expense')->value('id');

    Category::query()->create([
        'user_id' => $user->id,
        'category_type_id' => $incomeTypeId,
        'name' => 'Prihod App',
    ]);

    Category::query()->create([
        'user_id' => $user->id,
        'category_type_id' => $expenseTypeId,
        'name' => 'Trošak Cloud',
    ]);

    Livewire::actingAs($user)->test(Index::class)
        ->set('typeFilter', 'income')
        ->assertSee('Prihod App')
        ->assertDontSee('Trošak Cloud');
});

test('user can update category', function () {
    $user = User::factory()->create();
    $expenseTypeId = CategoryType::query()->where('key', 'expense')->value('id');

    $category = Category::query()->create([
        'user_id' => $user->id,
        'category_type_id' => $expenseTypeId,
        'name' => 'Stari naziv',
    ]);

    Livewire::actingAs($user)->test(Form::class, ['category' => $category])
        ->set('name', 'Novi naziv')
        ->call('save')
        ->assertRedirect(route('categories.index', absolute: false));

    $this->assertDatabaseHas('categories', [
        'id' => $category->id,
        'name' => 'Novi naziv',
    ]);
});

test('user can delete category', function () {
    $user = User::factory()->create();
    $expenseTypeId = CategoryType::query()->where('key', 'expense')->value('id');

    $category = Category::query()->create([
        'user_id' => $user->id,
        'category_type_id' => $expenseTypeId,
        'name' => 'Za brisanje',
    ]);

    Livewire::actingAs($user)->test(Index::class)
        ->call('deleteCategory', $category->id);

    $this->assertDatabaseMissing('categories', [
        'id' => $category->id,
    ]);
});
