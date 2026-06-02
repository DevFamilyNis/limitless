<?php

declare(strict_types=1);

use App\Domain\Dashboard\Queries\YearlyCashflowQuery;
use App\Models\Category;
use App\Models\CategoryType;
use App\Models\Transaction;
use App\Models\User;

beforeEach(function () {
    // CategoryTypes are seeded via migration (income/expense)
    $this->incomeType = CategoryType::query()->where('key', 'income')->first();
    $this->expenseType = CategoryType::query()->where('key', 'expense')->first();
    $this->user = User::factory()->create();

    $this->incomeCat = Category::query()->create(['user_id' => $this->user->id, 'category_type_id' => $this->incomeType->id, 'name' => 'Prihod Test']);
    $this->expenseCat = Category::query()->create(['user_id' => $this->user->id, 'category_type_id' => $this->expenseType->id, 'name' => 'Rashod Test']);
});

// ─── availableYears ───────────────────────────────────────────────────────────

test('available years returns only years that exist in transaction dates', function () {
    Transaction::query()->create(['user_id' => $this->user->id, 'category_id' => $this->incomeCat->id, 'date' => '2025-03-15', 'amount' => 1000, 'currency' => 'RSD', 'title' => 'T1']);
    Transaction::query()->create(['user_id' => $this->user->id, 'category_id' => $this->incomeCat->id, 'date' => '2026-06-01', 'amount' => 1000, 'currency' => 'RSD', 'title' => 'T2']);

    $years = (new YearlyCashflowQuery)->availableYears();

    expect($years)->toContain(2025);
    expect($years)->toContain(2026);
    expect($years)->not()->toContain(2024);
});

test('available years returns newest year first', function () {
    Transaction::query()->create(['user_id' => $this->user->id, 'category_id' => $this->incomeCat->id, 'date' => '2024-01-01', 'amount' => 100, 'currency' => 'RSD', 'title' => 'old']);
    Transaction::query()->create(['user_id' => $this->user->id, 'category_id' => $this->incomeCat->id, 'date' => '2026-01-01', 'amount' => 100, 'currency' => 'RSD', 'title' => 'new']);

    $years = (new YearlyCashflowQuery)->availableYears();

    expect($years[0])->toBe(2026);
    expect($years[1])->toBe(2024);
});

test('available years returns empty array when no transactions exist', function () {
    expect((new YearlyCashflowQuery)->availableYears())->toBeEmpty();
});

// ─── execute — structure ──────────────────────────────────────────────────────

test('execute returns exactly 12 months', function () {
    $result = (new YearlyCashflowQuery)->execute(2026);

    expect($result['months'])->toHaveCount(12);
});

test('each month has required keys', function () {
    $result = (new YearlyCashflowQuery)->execute(2026);

    foreach ($result['months'] as $month) {
        expect($month)->toHaveKeys(['month', 'label', 'income', 'expense', 'net']);
    }
});

test('months without transactions have income expense and net of zero', function () {
    // No transactions at all
    $result = (new YearlyCashflowQuery)->execute(2026);

    foreach ($result['months'] as $month) {
        expect($month['income'])->toBe(0.0);
        expect($month['expense'])->toBe(0.0);
        expect($month['net'])->toBe(0.0);
    }
});

// ─── execute — data correctness ───────────────────────────────────────────────

test('income is aggregated by transaction date month', function () {
    Transaction::query()->create(['user_id' => $this->user->id, 'category_id' => $this->incomeCat->id, 'date' => '2026-03-10', 'amount' => 50000, 'currency' => 'RSD', 'title' => 'Invoice A']);
    Transaction::query()->create(['user_id' => $this->user->id, 'category_id' => $this->incomeCat->id, 'date' => '2026-03-25', 'amount' => 30000, 'currency' => 'RSD', 'title' => 'Invoice B']);

    $result = (new YearlyCashflowQuery)->execute(2026);
    $march = $result['months'][2]; // index 2 = March

    expect($march['income'])->toBe(80000.0);
    expect($march['expense'])->toBe(0.0);
    expect($march['net'])->toBe(80000.0);
});

test('expense is aggregated by transaction date month', function () {
    Transaction::query()->create(['user_id' => $this->user->id, 'category_id' => $this->expenseCat->id, 'date' => '2026-05-05', 'amount' => 12000, 'currency' => 'RSD', 'title' => 'Rent']);
    Transaction::query()->create(['user_id' => $this->user->id, 'category_id' => $this->expenseCat->id, 'date' => '2026-05-20', 'amount' => 3000,  'currency' => 'RSD', 'title' => 'Internet']);

    $result = (new YearlyCashflowQuery)->execute(2026);
    $may = $result['months'][4]; // index 4 = May

    expect($may['expense'])->toBe(15000.0);
    expect($may['income'])->toBe(0.0);
    expect($may['net'])->toBe(-15000.0);
});

test('created_at does not affect monthly bucketing — only date column matters', function () {
    // Transaction with date in Jan but simulated old created_at
    Transaction::query()->create([
        'user_id' => $this->user->id,
        'category_id' => $this->incomeCat->id,
        'date' => '2026-01-15',
        'amount' => 20000,
        'currency' => 'RSD',
        'title' => 'Payment',
        'created_at' => '2025-12-01 10:00:00', // different year
        'updated_at' => '2025-12-01 10:00:00',
    ]);

    $result = (new YearlyCashflowQuery)->execute(2026);
    $january = $result['months'][0];

    expect($january['income'])->toBe(20000.0);
});

test('transactions from other years are excluded', function () {
    Transaction::query()->create(['user_id' => $this->user->id, 'category_id' => $this->incomeCat->id, 'date' => '2025-06-01', 'amount' => 99999, 'currency' => 'RSD', 'title' => 'Old']);
    Transaction::query()->create(['user_id' => $this->user->id, 'category_id' => $this->incomeCat->id, 'date' => '2026-06-01', 'amount' => 10000, 'currency' => 'RSD', 'title' => 'Current']);

    $result = (new YearlyCashflowQuery)->execute(2026);
    $june = $result['months'][5];

    expect($june['income'])->toBe(10000.0);
});

test('totals are sum of all monthly values', function () {
    Transaction::query()->create(['user_id' => $this->user->id, 'category_id' => $this->incomeCat->id,  'date' => '2026-01-01', 'amount' => 40000, 'currency' => 'RSD', 'title' => 'A']);
    Transaction::query()->create(['user_id' => $this->user->id, 'category_id' => $this->incomeCat->id,  'date' => '2026-02-01', 'amount' => 60000, 'currency' => 'RSD', 'title' => 'B']);
    Transaction::query()->create(['user_id' => $this->user->id, 'category_id' => $this->expenseCat->id, 'date' => '2026-01-01', 'amount' => 20000, 'currency' => 'RSD', 'title' => 'C']);

    $result = (new YearlyCashflowQuery)->execute(2026);

    expect($result['totals']['income'])->toBe(100000.0);
    expect($result['totals']['expense'])->toBe(20000.0);
    expect($result['totals']['net'])->toBe(80000.0);
});

// ─── investment signal ────────────────────────────────────────────────────────

function makeSafeYear(mixed $test, int $year): void
{
    // 10 months with income 50% above expense, 2 months empty
    for ($m = 1; $m <= 10; $m++) {
        $date = sprintf('%d-%02d-10', $year, $m);
        Transaction::query()->create(['user_id' => $test->user->id, 'category_id' => $test->incomeCat->id,  'date' => $date, 'amount' => 60000, 'currency' => 'RSD', 'title' => "Inc $m"]);
        Transaction::query()->create(['user_id' => $test->user->id, 'category_id' => $test->expenseCat->id, 'date' => $date, 'amount' => 40000, 'currency' => 'RSD', 'title' => "Exp $m"]);
    }
}

test('SAFE signal for stable profitable year', function () {
    makeSafeYear($this, 2026);

    $result = (new YearlyCashflowQuery)->execute(2026);

    expect($result['signal']['status'])->toBe('safe');
    expect($result['signal']['label'])->toBe('Moguća investicija');
    expect($result['signal']['recommended_max_investment'])->toBeGreaterThan(0);
});

test('CAUTION signal for positive but unstable year', function () {
    // Only 5 positive months, others are zero net
    for ($m = 1; $m <= 5; $m++) {
        $date = sprintf('2026-%02d-10', $m);
        Transaction::query()->create(['user_id' => $this->user->id, 'category_id' => $this->incomeCat->id,  'date' => $date, 'amount' => 30000, 'currency' => 'RSD', 'title' => "Inc $m"]);
        Transaction::query()->create(['user_id' => $this->user->id, 'category_id' => $this->expenseCat->id, 'date' => $date, 'amount' => 25000, 'currency' => 'RSD', 'title' => "Exp $m"]);
    }

    $result = (new YearlyCashflowQuery)->execute(2026);

    expect($result['signal']['status'])->toBe('caution');
    expect($result['signal']['recommended_max_investment'])->toBeGreaterThan(0);
});

test('UNSAFE signal when annual net is negative', function () {
    // All months: expense > income
    for ($m = 1; $m <= 6; $m++) {
        $date = sprintf('2026-%02d-10', $m);
        Transaction::query()->create(['user_id' => $this->user->id, 'category_id' => $this->incomeCat->id,  'date' => $date, 'amount' => 20000, 'currency' => 'RSD', 'title' => "Inc $m"]);
        Transaction::query()->create(['user_id' => $this->user->id, 'category_id' => $this->expenseCat->id, 'date' => $date, 'amount' => 35000, 'currency' => 'RSD', 'title' => "Exp $m"]);
    }

    $result = (new YearlyCashflowQuery)->execute(2026);

    expect($result['signal']['status'])->toBe('unsafe');
    expect($result['signal']['label'])->toBe('Nije preporučeno');
});

test('recommended max investment is 0 when signal is unsafe', function () {
    // Negative net
    Transaction::query()->create(['user_id' => $this->user->id, 'category_id' => $this->expenseCat->id, 'date' => '2026-01-01', 'amount' => 50000, 'currency' => 'RSD', 'title' => 'Big expense']);

    $result = (new YearlyCashflowQuery)->execute(2026);

    expect($result['signal']['status'])->toBe('unsafe');
    expect($result['signal']['recommended_max_investment'])->toBe(0.0);
});

test('UNSAFE signal when no data exists for year', function () {
    $result = (new YearlyCashflowQuery)->execute(2099);

    expect($result['signal']['status'])->toBe('unsafe');
    expect($result['signal']['recommended_max_investment'])->toBe(0.0);
});
