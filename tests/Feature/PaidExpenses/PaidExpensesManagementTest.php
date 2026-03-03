<?php

use App\Livewire\PaidExpenses\Index;
use App\Models\Category;
use App\Models\CategoryType;
use App\Models\Transaction;
use App\Models\User;
use Livewire\Livewire;

test('paid expenses page is displayed', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->get(route('paid-expenses.index'))
        ->assertOk()
        ->assertSee('Plaćeni rashodi');
});

test('user can create paid expense', function () {
    $user = User::factory()->create();
    $expenseTypeId = CategoryType::query()->where('key', 'expense')->value('id');

    $expenseCategory = Category::query()->create([
        'user_id' => $user->id,
        'category_type_id' => $expenseTypeId,
        'name' => 'Zakup kancelarije',
    ]);

    Livewire::actingAs($user)->test(Index::class)
        ->set('categoryId', (string) $expenseCategory->id)
        ->set('date', '2026-03-02')
        ->set('title', 'Račun za zakup')
        ->set('amount', '25000')
        ->set('note', 'Plaćeno preko računa')
        ->call('saveExpense');

    $transaction = Transaction::query()
        ->where('title', 'Račun za zakup')
        ->first();

    expect($transaction)->not->toBeNull();
    expect($transaction->category_id)->toBe($expenseCategory->id);
    expect($transaction->client_id)->toBeNull();
    expect($transaction->invoice_id)->toBeNull();
    expect($transaction->date?->toDateString())->toBe('2026-03-02');
    expect((float) $transaction->amount)->toBe(25000.0);
    expect($transaction->currency)->toBe('RSD');
});

test('user can update paid expense', function () {
    $user = User::factory()->create();
    $expenseTypeId = CategoryType::query()->where('key', 'expense')->value('id');

    $categoryA = Category::query()->create([
        'user_id' => $user->id,
        'category_type_id' => $expenseTypeId,
        'name' => 'Zakup',
    ]);

    $categoryB = Category::query()->create([
        'user_id' => $user->id,
        'category_type_id' => $expenseTypeId,
        'name' => 'Internet',
    ]);

    $expense = Transaction::query()->create([
        'user_id' => $user->id,
        'category_id' => $categoryA->id,
        'client_id' => null,
        'invoice_id' => null,
        'date' => '2026-03-01',
        'amount' => 5000,
        'currency' => 'RSD',
        'title' => 'Stari naslov',
        'note' => null,
    ]);

    Livewire::actingAs($user)->test(Index::class)
        ->call('editExpense', $expense->id)
        ->set('categoryId', (string) $categoryB->id)
        ->set('title', 'Ažuriran rashod')
        ->set('amount', '7200')
        ->call('saveExpense');

    $this->assertDatabaseHas('transactions', [
        'id' => $expense->id,
        'category_id' => $categoryB->id,
        'title' => 'Ažuriran rashod',
        'amount' => '7200.00',
    ]);
});

test('user can delete paid expense', function () {
    $user = User::factory()->create();
    $expenseTypeId = CategoryType::query()->where('key', 'expense')->value('id');

    $expenseCategory = Category::query()->create([
        'user_id' => $user->id,
        'category_type_id' => $expenseTypeId,
        'name' => 'Telefon',
    ]);

    $expense = Transaction::query()->create([
        'user_id' => $user->id,
        'category_id' => $expenseCategory->id,
        'client_id' => null,
        'invoice_id' => null,
        'date' => now()->toDateString(),
        'amount' => 3400,
        'currency' => 'RSD',
        'title' => 'Telefon mart',
        'note' => null,
    ]);

    Livewire::actingAs($user)->test(Index::class)
        ->call('deleteExpense', $expense->id);

    $this->assertDatabaseMissing('transactions', [
        'id' => $expense->id,
    ]);
});

test('paid expenses page filters by month and year', function () {
    $user = User::factory()->create();
    $expenseTypeId = CategoryType::query()->where('key', 'expense')->value('id');

    $expenseCategory = Category::query()->create([
        'user_id' => $user->id,
        'category_type_id' => $expenseTypeId,
        'name' => 'Komunalije',
    ]);

    Transaction::query()->create([
        'user_id' => $user->id,
        'category_id' => $expenseCategory->id,
        'client_id' => null,
        'invoice_id' => null,
        'date' => '2026-02-10',
        'amount' => 4000,
        'currency' => 'RSD',
        'title' => 'Februar rashod',
        'note' => null,
    ]);

    Transaction::query()->create([
        'user_id' => $user->id,
        'category_id' => $expenseCategory->id,
        'client_id' => null,
        'invoice_id' => null,
        'date' => '2026-03-10',
        'amount' => 4500,
        'currency' => 'RSD',
        'title' => 'Mart rashod',
        'note' => null,
    ]);

    Livewire::actingAs($user)->test(Index::class)
        ->set('month', '02')
        ->set('year', '2026')
        ->assertSee('Februar rashod')
        ->assertDontSee('Mart rashod');
});
