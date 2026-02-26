<?php

use App\Livewire\MonthlyExpenses\Index;
use App\Models\BillingPeriod;
use App\Models\MonthlyExpenseItem;
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
    $monthlyId = BillingPeriod::query()->where('key', 'monthly')->value('id');

    Livewire::actingAs($user)->test(Index::class)
        ->set('billingPeriodId', (string) $monthlyId)
        ->set('title', 'Doprinosi februar')
        ->set('amount', '25000')
        ->set('note', 'Mesečna obaveza')
        ->call('saveItem')
        ->assertSee('Doprinosi februar');

    $this->assertDatabaseHas('monthly_expense_items', [
        'user_id' => $user->id,
        'billing_period_id' => $monthlyId,
        'title' => 'Doprinosi februar',
        'amount' => 25000,
    ]);
});

test('user can update monthly expense', function () {
    $user = User::factory()->create();
    $monthlyId = BillingPeriod::query()->where('key', 'monthly')->value('id');

    $expense = MonthlyExpenseItem::query()->create([
        'user_id' => $user->id,
        'billing_period_id' => $monthlyId,
        'amount' => 60000,
        'title' => 'Renta kancelarije',
        'note' => null,
    ]);

    Livewire::actingAs($user)->test(Index::class)
        ->call('editItem', $expense->id)
        ->set('amount', '65000')
        ->set('title', 'Renta kancelarije + troškovi')
        ->call('saveItem')
        ->assertSee('Renta kancelarije + troškovi');

    $this->assertDatabaseHas('monthly_expense_items', [
        'id' => $expense->id,
        'amount' => 65000,
        'title' => 'Renta kancelarije + troškovi',
    ]);
});

test('user can delete monthly expense', function () {
    $user = User::factory()->create();
    $monthlyId = BillingPeriod::query()->where('key', 'monthly')->value('id');

    $expense = MonthlyExpenseItem::query()->create([
        'user_id' => $user->id,
        'billing_period_id' => $monthlyId,
        'amount' => 7000,
        'title' => 'Račun za struju',
        'note' => null,
    ]);

    Livewire::actingAs($user)->test(Index::class)
        ->call('deleteItem', $expense->id)
        ->assertDontSee('Račun za struju');

    $this->assertDatabaseMissing('monthly_expense_items', [
        'id' => $expense->id,
    ]);
});

test('monthly expenses page shows monthly equivalent total', function () {
    $user = User::factory()->create();
    $monthlyId = BillingPeriod::query()->where('key', 'monthly')->value('id');
    $yearlyId = BillingPeriod::query()->where('key', 'yearly')->value('id');

    MonthlyExpenseItem::query()->create([
        'user_id' => $user->id,
        'billing_period_id' => $monthlyId,
        'amount' => 1200,
        'title' => 'Internet',
        'note' => null,
    ]);

    MonthlyExpenseItem::query()->create([
        'user_id' => $user->id,
        'billing_period_id' => $yearlyId,
        'amount' => 12000,
        'title' => 'Hosting godišnje',
        'note' => null,
    ]);

    Livewire::actingAs($user)->test(Index::class)
        ->assertSee('Internet')
        ->assertSee('Hosting godišnje')
        ->assertSee('2.200,00 RSD');
});
