<?php

declare(strict_types=1);

use App\Enums\PermissionKey;
use App\Enums\RoleKey;
use App\Livewire\MonthlyExpenses\Index as MonthlyExpenseIndex;
use App\Livewire\PaidExpenses\Index as PaidExpenseIndex;
use App\Livewire\Transactions\Form as TransactionForm;
use App\Models\BillingPeriod;
use App\Models\Category;
use App\Models\CategoryType;
use App\Models\MonthlyExpenseItem;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Auth\Access\AuthorizationException;

// All three views use Flux UI. Direct component instantiation avoids view rendering.
// Transactions/Form has no registered route — guard is tested via direct instantiation only.
// "Can" path for Transactions/Form is implicitly covered by MonthlyExpenses/PaidExpenses tests
// which share the same ManageTransactions permission gate.

beforeEach(function () {
    app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();
    (new \Database\Seeders\RolesAndPermissionsSeeder)->run();
});

// ─── CANNOT: user without manage-transactions ─────────────────────────────────

test('user without manage-transactions cannot save monthly expense item', function () {
    $user = User::factory()->create();
    $this->actingAs($user);

    $initialCount = MonthlyExpenseItem::query()->count();

    expect(fn () => (new MonthlyExpenseIndex)->saveItem())
        ->toThrow(AuthorizationException::class);

    expect(MonthlyExpenseItem::query()->count())->toBe($initialCount);
});

test('user without manage-transactions cannot delete monthly expense item', function () {
    $user = User::factory()->create();
    $this->actingAs($user);

    $monthlyId = BillingPeriod::query()->where('key', 'monthly')->value('id');
    $item = MonthlyExpenseItem::query()->create([
        'user_id' => $user->id,
        'billing_period_id' => $monthlyId,
        'amount' => 5000,
        'title' => 'Test stavka',
    ]);

    expect(fn () => (new MonthlyExpenseIndex)->deleteItem($item->id))
        ->toThrow(AuthorizationException::class);

    expect(MonthlyExpenseItem::find($item->id))->not()->toBeNull();
});

test('user without manage-transactions cannot save paid expense', function () {
    $user = User::factory()->create();
    $this->actingAs($user);

    $initialCount = Transaction::query()->count();

    expect(fn () => (new PaidExpenseIndex)->saveExpense())
        ->toThrow(AuthorizationException::class);

    expect(Transaction::query()->count())->toBe($initialCount);
});

test('user without manage-transactions cannot delete paid expense', function () {
    $user = User::factory()->create();
    $this->actingAs($user);

    $expenseTypeId = CategoryType::query()->where('key', 'expense')->value('id');
    $category = Category::query()->create([
        'user_id' => $user->id,
        'category_type_id' => $expenseTypeId,
        'name' => 'Test kategorija',
    ]);

    $transaction = Transaction::query()->create([
        'user_id' => $user->id,
        'category_id' => $category->id,
        'date' => now()->toDateString(),
        'amount' => 3000,
        'currency' => 'RSD',
        'title' => 'Test rashod',
    ]);

    expect(fn () => (new PaidExpenseIndex)->deleteExpense($transaction->id))
        ->toThrow(AuthorizationException::class);

    expect(Transaction::find($transaction->id))->not()->toBeNull();
});

test('user without manage-transactions cannot save transaction form', function () {
    $user = User::factory()->create();
    $this->actingAs($user);

    $initialCount = Transaction::query()->count();

    expect(fn () => (new TransactionForm)->save())
        ->toThrow(AuthorizationException::class);

    expect(Transaction::query()->count())->toBe($initialCount);
});

// ─── CAN: user with manage-transactions ──────────────────────────────────────

test('user with manage-transactions can save monthly expense item', function () {
    $user = User::factory()->create();
    $user->givePermissionTo(PermissionKey::ManageTransactions->value);
    $this->actingAs($user);

    $monthlyId = BillingPeriod::query()->where('key', 'monthly')->value('id');

    $component = new MonthlyExpenseIndex;
    $component->billingPeriodId = (string) $monthlyId;
    $component->title = 'Doprinosi';
    $component->amount = '25000';

    $component->saveItem();

    expect(MonthlyExpenseItem::query()
        ->where('user_id', $user->id)
        ->where('title', 'Doprinosi')
        ->exists()
    )->toBeTrue();
});

test('user with manage-transactions can save paid expense', function () {
    $user = User::factory()->create();
    $user->givePermissionTo(PermissionKey::ManageTransactions->value);
    $this->actingAs($user);

    $expenseTypeId = CategoryType::query()->where('key', 'expense')->value('id');
    $category = Category::query()->create([
        'user_id' => $user->id,
        'category_type_id' => $expenseTypeId,
        'name' => 'Računovodstvo',
    ]);

    $component = new PaidExpenseIndex;
    $component->categoryId = (string) $category->id;
    $component->date = now()->toDateString();
    $component->amount = '12000';
    $component->title = 'Mesečna naknada';

    $component->saveExpense();

    expect(Transaction::query()
        ->where('user_id', $user->id)
        ->where('title', 'Mesečna naknada')
        ->exists()
    )->toBeTrue();
});

// ─── SUPER-ADMIN: Gate::before bypass ────────────────────────────────────────

test('super-admin can save monthly expense item via gate bypass', function () {
    $superAdmin = User::factory()->create();
    $superAdmin->assignRole(RoleKey::SuperAdmin->value);
    $this->actingAs($superAdmin);

    $monthlyId = BillingPeriod::query()->where('key', 'monthly')->value('id');

    $component = new MonthlyExpenseIndex;
    $component->billingPeriodId = (string) $monthlyId;
    $component->title = 'Super Admin stavka';
    $component->amount = '10000';

    $component->saveItem();

    expect(MonthlyExpenseItem::query()
        ->where('user_id', $superAdmin->id)
        ->where('title', 'Super Admin stavka')
        ->exists()
    )->toBeTrue();
});
