<?php

use App\Livewire\Transactions\Index;
use App\Models\Category;
use App\Models\CategoryType;
use App\Models\Transaction;
use App\Models\User;
use Livewire\Livewire;

test('transactions page is displayed as read only report', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->get(route('transactions.index'))
        ->assertOk()
        ->assertDontSee('Dodaj');
});

test('create and edit transaction urls are not available', function () {
    $user = User::factory()->create();

    $this->actingAs($user)->get('/transactions/create')->assertNotFound();
    $this->actingAs($user)->get('/transactions/1/edit')->assertNotFound();
});

test('user can filter transactions by month and year', function () {
    $user = User::factory()->create();
    $expenseTypeId = CategoryType::query()->where('key', 'expense')->value('id');

    $category = Category::query()->create([
        'user_id' => $user->id,
        'category_type_id' => $expenseTypeId,
        'name' => 'Zakup',
    ]);

    Transaction::query()->create([
        'user_id' => $user->id,
        'category_id' => $category->id,
        'client_id' => null,
        'invoice_id' => null,
        'date' => '2025-12-15',
        'amount' => 1000,
        'currency' => 'RSD',
        'title' => 'Decembar 2025',
    ]);

    Transaction::query()->create([
        'user_id' => $user->id,
        'category_id' => $category->id,
        'client_id' => null,
        'invoice_id' => null,
        'date' => '2026-01-10',
        'amount' => 1500,
        'currency' => 'RSD',
        'title' => 'Januar 2026',
    ]);

    Livewire::actingAs($user)->test(Index::class)
        ->set('month', '12')
        ->set('year', '2025')
        ->assertSee('Decembar 2025')
        ->assertDontSee('Januar 2026');
});

test('user can filter transactions by category type key', function () {
    $user = User::factory()->create();
    $incomeTypeId = CategoryType::query()->where('key', 'income')->value('id');
    $expenseTypeId = CategoryType::query()->where('key', 'expense')->value('id');

    $incomeCategory = Category::query()->create([
        'user_id' => $user->id,
        'category_type_id' => $incomeTypeId,
        'name' => 'Prihod',
    ]);

    $expenseCategory = Category::query()->create([
        'user_id' => $user->id,
        'category_type_id' => $expenseTypeId,
        'name' => 'Rashod',
    ]);

    Transaction::query()->create([
        'user_id' => $user->id,
        'category_id' => $incomeCategory->id,
        'client_id' => null,
        'invoice_id' => null,
        'date' => now()->toDateString(),
        'amount' => 1000,
        'currency' => 'RSD',
        'title' => 'Prihod transakcija',
    ]);

    Transaction::query()->create([
        'user_id' => $user->id,
        'category_id' => $expenseCategory->id,
        'client_id' => null,
        'invoice_id' => null,
        'date' => now()->toDateString(),
        'amount' => 500,
        'currency' => 'RSD',
        'title' => 'Rashod transakcija',
    ]);

    Livewire::actingAs($user)->test(Index::class)
        ->set('typeFilter', 'expense')
        ->assertSee('Rashod transakcija')
        ->assertDontSee('Prihod transakcija');
});
