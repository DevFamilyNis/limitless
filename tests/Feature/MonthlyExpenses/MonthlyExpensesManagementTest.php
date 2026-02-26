<?php

use App\Livewire\MonthlyExpenses\Index;
use App\Models\Category;
use App\Models\CategoryType;
use App\Models\Transaction;
use App\Models\User;
use Livewire\Livewire;

test('monthly expenses page is displayed', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->get(route('monthly-expenses.index'))
        ->assertOk()
        ->assertSee('Mesečni rashodi');
});

test('user can create monthly expense', function () {
    $user = User::factory()->create();
    $expenseTypeId = CategoryType::query()->where('key', 'expense')->value('id');

    $category = Category::query()->create([
        'user_id' => $user->id,
        'category_type_id' => $expenseTypeId,
        'name' => 'Doprinosi',
    ]);

    Livewire::actingAs($user)->test(Index::class)
        ->set('categoryId', (string) $category->id)
        ->set('date', '2026-02-10')
        ->set('title', 'Doprinosi februar')
        ->set('amount', '25000')
        ->set('note', 'Mesečna obaveza')
        ->call('saveExpense')
        ->assertSee('Doprinosi februar');

    $this->assertDatabaseHas('transactions', [
        'user_id' => $user->id,
        'category_id' => $category->id,
        'title' => 'Doprinosi februar',
        'amount' => 25000,
        'currency' => 'RSD',
    ]);
});

test('user can update monthly expense', function () {
    $user = User::factory()->create();
    $expenseTypeId = CategoryType::query()->where('key', 'expense')->value('id');

    $category = Category::query()->create([
        'user_id' => $user->id,
        'category_type_id' => $expenseTypeId,
        'name' => 'Renta',
    ]);

    $expense = Transaction::query()->create([
        'user_id' => $user->id,
        'category_id' => $category->id,
        'client_id' => null,
        'invoice_id' => null,
        'date' => '2026-02-05',
        'amount' => 60000,
        'currency' => 'RSD',
        'title' => 'Renta kancelarije',
        'note' => null,
    ]);

    Livewire::actingAs($user)->test(Index::class)
        ->call('editExpense', $expense->id)
        ->set('amount', '65000')
        ->set('title', 'Renta kancelarije + troškovi')
        ->call('saveExpense')
        ->assertSee('Renta kancelarije + troškovi');

    $this->assertDatabaseHas('transactions', [
        'id' => $expense->id,
        'amount' => 65000,
        'title' => 'Renta kancelarije + troškovi',
    ]);
});

test('user can delete monthly expense', function () {
    $user = User::factory()->create();
    $expenseTypeId = CategoryType::query()->where('key', 'expense')->value('id');

    $category = Category::query()->create([
        'user_id' => $user->id,
        'category_type_id' => $expenseTypeId,
        'name' => 'Komunalije',
    ]);

    $expense = Transaction::query()->create([
        'user_id' => $user->id,
        'category_id' => $category->id,
        'client_id' => null,
        'invoice_id' => null,
        'date' => now()->toDateString(),
        'amount' => 7000,
        'currency' => 'RSD',
        'title' => 'Račun za struju',
        'note' => null,
    ]);

    Livewire::actingAs($user)->test(Index::class)
        ->call('deleteExpense', $expense->id)
        ->assertDontSee('Račun za struju');

    $this->assertDatabaseMissing('transactions', [
        'id' => $expense->id,
    ]);
});

test('monthly expenses page shows total for selected month and only expense transactions', function () {
    $user = User::factory()->create();
    $expenseTypeId = CategoryType::query()->where('key', 'expense')->value('id');
    $incomeTypeId = CategoryType::query()->where('key', 'income')->value('id');

    $expenseCategory = Category::query()->create([
        'user_id' => $user->id,
        'category_type_id' => $expenseTypeId,
        'name' => 'Operativni troškovi',
    ]);

    $incomeCategory = Category::query()->create([
        'user_id' => $user->id,
        'category_type_id' => $incomeTypeId,
        'name' => 'Naplata',
    ]);

    Transaction::query()->create([
        'user_id' => $user->id,
        'category_id' => $expenseCategory->id,
        'client_id' => null,
        'invoice_id' => null,
        'date' => '2026-02-03',
        'amount' => 1200,
        'currency' => 'RSD',
        'title' => 'Internet',
        'note' => null,
    ]);

    Transaction::query()->create([
        'user_id' => $user->id,
        'category_id' => $expenseCategory->id,
        'client_id' => null,
        'invoice_id' => null,
        'date' => '2026-02-10',
        'amount' => 2300,
        'currency' => 'RSD',
        'title' => 'Telefon',
        'note' => null,
    ]);

    Transaction::query()->create([
        'user_id' => $user->id,
        'category_id' => $incomeCategory->id,
        'client_id' => null,
        'invoice_id' => null,
        'date' => '2026-02-12',
        'amount' => 50000,
        'currency' => 'RSD',
        'title' => 'Uplata klijenta',
        'note' => null,
    ]);

    Livewire::actingAs($user)->test(Index::class)
        ->set('month', '02')
        ->set('year', '2026')
        ->assertSee('Internet')
        ->assertSee('Telefon')
        ->assertDontSee('Uplata klijenta')
        ->assertSee('3.500,00 RSD');
});
