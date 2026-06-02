<?php

use App\Domain\MonthlyIncomes\Actions\DeleteMonthlyIncomeItemAction;
use App\Domain\MonthlyIncomes\Actions\UpsertMonthlyIncomeItemAction;
use App\Domain\MonthlyIncomes\DTO\DeleteMonthlyIncomeItemData;
use App\Domain\MonthlyIncomes\DTO\UpsertMonthlyIncomeItemData;
use App\Models\BillingPeriod;
use App\Models\MonthlyIncomeItem;
use App\Models\User;
use Illuminate\Database\Eloquent\ModelNotFoundException;

test('upsert monthly income item action creates and updates item', function () {
    $user = User::factory()->create();
    $monthlyId = BillingPeriod::query()->where('key', 'monthly')->value('id');
    $yearlyId = BillingPeriod::query()->where('key', 'yearly')->value('id');

    $item = app(UpsertMonthlyIncomeItemAction::class)->execute(
        UpsertMonthlyIncomeItemData::fromArray([
            'user_id' => $user->id,
            'billing_period_id' => $monthlyId,
            'name' => 'Ugovorni prihod',
            'price' => 75000,
            'description' => 'Retainer',
        ])
    );

    expect($item)->toBeInstanceOf(MonthlyIncomeItem::class);
    expect($item->billing_period_id)->toBe($monthlyId);
    expect((float) $item->price)->toBe(75000.0);

    $updated = app(UpsertMonthlyIncomeItemAction::class)->execute(
        UpsertMonthlyIncomeItemData::fromArray([
            'user_id' => $user->id,
            'item_id' => $item->id,
            'billing_period_id' => $yearlyId,
            'name' => 'Godišnji ugovor',
            'price' => 900000,
            'description' => '',
        ])
    );

    expect($updated->id)->toBe($item->id);
    expect($updated->billing_period_id)->toBe($yearlyId);
    expect((float) $updated->price)->toBe(900000.0);
    expect($updated->description)->toBeNull();
});

test('delete monthly income item action deletes user item', function () {
    $user = User::factory()->create();
    $monthlyId = BillingPeriod::query()->where('key', 'monthly')->value('id');

    $item = MonthlyIncomeItem::query()->create([
        'user_id' => $user->id,
        'billing_period_id' => $monthlyId,
        'name' => 'Prihod za brisanje',
        'price' => 5000,
        'description' => null,
    ]);

    app(DeleteMonthlyIncomeItemAction::class)->execute(
        DeleteMonthlyIncomeItemData::fromArray([
            'user_id' => $user->id,
            'item_id' => $item->id,
        ])
    );

    $this->assertDatabaseMissing('monthly_income_items', [
        'id' => $item->id,
    ]);
});

test('delete monthly income item action prevents deleting another users item', function () {
    $owner = User::factory()->create();
    $attacker = User::factory()->create();
    $monthlyId = BillingPeriod::query()->where('key', 'monthly')->value('id');

    $item = MonthlyIncomeItem::query()->create([
        'user_id' => $owner->id,
        'billing_period_id' => $monthlyId,
        'name' => 'Privatni prihod',
        'price' => 50000,
        'description' => null,
    ]);

    expect(fn () => app(DeleteMonthlyIncomeItemAction::class)->execute(
        DeleteMonthlyIncomeItemData::fromArray([
            'user_id' => $attacker->id,
            'item_id' => $item->id,
        ])
    ))->toThrow(ModelNotFoundException::class);
});
