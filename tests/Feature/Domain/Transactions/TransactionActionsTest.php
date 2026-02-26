<?php

use App\Domain\Transactions\Actions\DeleteTransactionAction;
use App\Domain\Transactions\DTO\DeleteTransactionData;
use App\Models\Category;
use App\Models\CategoryType;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Database\Eloquent\ModelNotFoundException;

test('delete transaction action deletes user transaction', function () {
    $user = User::factory()->create();
    $expenseTypeId = CategoryType::query()->where('key', 'expense')->value('id');

    $category = Category::query()->create([
        'user_id' => $user->id,
        'category_type_id' => $expenseTypeId,
        'name' => 'Trošak',
    ]);

    $transaction = Transaction::query()->create([
        'user_id' => $user->id,
        'category_id' => $category->id,
        'client_id' => null,
        'invoice_id' => null,
        'date' => now()->toDateString(),
        'amount' => 1200,
        'currency' => 'RSD',
        'title' => 'Trošak test',
        'note' => null,
    ]);

    app(DeleteTransactionAction::class)->execute(
        DeleteTransactionData::fromArray([
            'user_id' => $user->id,
            'transaction_id' => $transaction->id,
        ])
    );

    $this->assertDatabaseMissing('transactions', [
        'id' => $transaction->id,
    ]);
});

test('delete transaction action prevents deleting another users transaction', function () {
    $owner = User::factory()->create();
    $attacker = User::factory()->create();
    $expenseTypeId = CategoryType::query()->where('key', 'expense')->value('id');

    $category = Category::query()->create([
        'user_id' => $owner->id,
        'category_type_id' => $expenseTypeId,
        'name' => 'Trošak vlasnika',
    ]);

    $transaction = Transaction::query()->create([
        'user_id' => $owner->id,
        'category_id' => $category->id,
        'client_id' => null,
        'invoice_id' => null,
        'date' => now()->toDateString(),
        'amount' => 900,
        'currency' => 'RSD',
        'title' => 'Privatan trošak',
        'note' => null,
    ]);

    expect(fn () => app(DeleteTransactionAction::class)->execute(
        DeleteTransactionData::fromArray([
            'user_id' => $attacker->id,
            'transaction_id' => $transaction->id,
        ])
    ))->toThrow(ModelNotFoundException::class);
});
