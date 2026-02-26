<?php

use App\Domain\MonthlyExpenses\Actions\DeleteMonthlyExpenseItemAction;
use App\Domain\MonthlyExpenses\Actions\UpsertMonthlyExpenseItemAction;
use App\Domain\MonthlyExpenses\DTO\DeleteMonthlyExpenseItemData;
use App\Domain\MonthlyExpenses\DTO\UpsertMonthlyExpenseItemData;
use App\Models\BillingPeriod;
use App\Models\MonthlyExpenseItem;
use App\Models\User;
use Illuminate\Database\Eloquent\ModelNotFoundException;

test('upsert monthly expense item action creates and updates item', function () {
    $user = User::factory()->create();
    $monthlyId = BillingPeriod::query()->where('key', 'monthly')->value('id');
    $yearlyId = BillingPeriod::query()->where('key', 'yearly')->value('id');

    $item = app(UpsertMonthlyExpenseItemAction::class)->execute(
        UpsertMonthlyExpenseItemData::fromArray([
            'user_id' => $user->id,
            'billing_period_id' => $monthlyId,
            'title' => 'Renta',
            'amount' => 50000,
            'note' => 'Ugovor',
        ])
    );

    expect($item)->toBeInstanceOf(MonthlyExpenseItem::class);
    expect($item->billing_period_id)->toBe($monthlyId);
    expect((float) $item->amount)->toBe(50000.0);

    $updated = app(UpsertMonthlyExpenseItemAction::class)->execute(
        UpsertMonthlyExpenseItemData::fromArray([
            'user_id' => $user->id,
            'item_id' => $item->id,
            'billing_period_id' => $yearlyId,
            'title' => 'Renta godišnje',
            'amount' => 600000,
            'note' => '',
        ])
    );

    expect($updated->id)->toBe($item->id);
    expect($updated->billing_period_id)->toBe($yearlyId);
    expect((float) $updated->amount)->toBe(600000.0);
});

test('delete monthly expense item action deletes user item', function () {
    $user = User::factory()->create();
    $monthlyId = BillingPeriod::query()->where('key', 'monthly')->value('id');

    $item = MonthlyExpenseItem::query()->create([
        'user_id' => $user->id,
        'billing_period_id' => $monthlyId,
        'title' => 'Internet',
        'amount' => 3500,
        'note' => null,
    ]);

    app(DeleteMonthlyExpenseItemAction::class)->execute(
        DeleteMonthlyExpenseItemData::fromArray([
            'user_id' => $user->id,
            'item_id' => $item->id,
        ])
    );

    $this->assertDatabaseMissing('monthly_expense_items', [
        'id' => $item->id,
    ]);
});

test('delete monthly expense item action prevents deleting another users item', function () {
    $owner = User::factory()->create();
    $attacker = User::factory()->create();
    $monthlyId = BillingPeriod::query()->where('key', 'monthly')->value('id');

    $item = MonthlyExpenseItem::query()->create([
        'user_id' => $owner->id,
        'billing_period_id' => $monthlyId,
        'title' => 'Privatan trošak',
        'amount' => 900,
        'note' => null,
    ]);

    expect(fn () => app(DeleteMonthlyExpenseItemAction::class)->execute(
        DeleteMonthlyExpenseItemData::fromArray([
            'user_id' => $attacker->id,
            'item_id' => $item->id,
        ])
    ))->toThrow(ModelNotFoundException::class);
});
