<?php

use App\Livewire\MonthlyIncomes\Index;
use App\Models\BillingPeriod;
use App\Models\MonthlyIncomeItem;
use App\Models\User;
use Livewire\Livewire;

test('monthly incomes page is displayed', function () {
    $user = User::factory()->create();

    $this->actingAsWithSession($user)
        ->get(route('monthly-incomes.index'))
        ->assertOk()
        ->assertSee('Redovni mesečni prihodi');
});

test('user can create monthly income', function () {
    $user = User::factory()->create();
    $monthlyId = BillingPeriod::query()->where('key', 'monthly')->value('id');

    Livewire::actingAs($user)->test(Index::class)
        ->set('billingPeriodId', (string) $monthlyId)
        ->set('name', 'Ugovor sa klijentom A')
        ->set('price', '120000')
        ->set('description', 'Mesečna naknada')
        ->call('saveItem')
        ->assertSee('Ugovor sa klijentom A');

    $this->assertDatabaseHas('monthly_income_items', [
        'user_id' => $user->id,
        'billing_period_id' => $monthlyId,
        'name' => 'Ugovor sa klijentom A',
        'price' => 120000,
    ]);
});

test('user can update monthly income', function () {
    $user = User::factory()->create();
    $monthlyId = BillingPeriod::query()->where('key', 'monthly')->value('id');

    $income = MonthlyIncomeItem::query()->create([
        'user_id' => $user->id,
        'billing_period_id' => $monthlyId,
        'name' => 'Retainer klijent B',
        'price' => 80000,
        'description' => null,
    ]);

    Livewire::actingAs($user)->test(Index::class)
        ->call('editItem', $income->id)
        ->set('price', '95000')
        ->set('name', 'Retainer klijent B - nova cena')
        ->call('saveItem')
        ->assertSee('Retainer klijent B - nova cena');

    $this->assertDatabaseHas('monthly_income_items', [
        'id' => $income->id,
        'price' => 95000,
        'name' => 'Retainer klijent B - nova cena',
    ]);
});

test('user can delete monthly income', function () {
    $user = User::factory()->create();
    $monthlyId = BillingPeriod::query()->where('key', 'monthly')->value('id');

    $income = MonthlyIncomeItem::query()->create([
        'user_id' => $user->id,
        'billing_period_id' => $monthlyId,
        'name' => 'Privremeni prihod',
        'price' => 15000,
        'description' => null,
    ]);

    Livewire::actingAs($user)->test(Index::class)
        ->call('deleteItem', $income->id)
        ->assertDontSee('Privremeni prihod');

    $this->assertDatabaseMissing('monthly_income_items', [
        'id' => $income->id,
    ]);
});

test('monthly incomes page shows monthly equivalent total', function () {
    $user = User::factory()->create();
    $monthlyId = BillingPeriod::query()->where('key', 'monthly')->value('id');
    $yearlyId = BillingPeriod::query()->where('key', 'yearly')->value('id');

    MonthlyIncomeItem::query()->create([
        'user_id' => $user->id,
        'billing_period_id' => $monthlyId,
        'name' => 'Mesečni retainer',
        'price' => 1200,
        'description' => null,
    ]);

    MonthlyIncomeItem::query()->create([
        'user_id' => $user->id,
        'billing_period_id' => $yearlyId,
        'name' => 'Godišnji ugovor',
        'price' => 12000,
        'description' => null,
    ]);

    Livewire::actingAs($user)->test(Index::class)
        ->assertSee('Mesečni retainer')
        ->assertSee('Godišnji ugovor')
        ->assertSee('2.200,00 RSD');
});
