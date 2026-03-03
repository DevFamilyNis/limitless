<?php

declare(strict_types=1);

namespace App\Domain\PaidExpenses\Actions;

use App\Domain\PaidExpenses\DTO\UpsertPaidExpenseData;
use App\Models\Category;
use App\Models\Transaction;

final class UpsertPaidExpenseAction
{
    public function execute(UpsertPaidExpenseData $dto): Transaction
    {
        $category = Category::query()
            ->whereHas('type', fn ($query) => $query->where('key', 'expense'))
            ->findOrFail($dto->categoryId);

        $transaction = $dto->transactionId
            ? Transaction::query()->findOrFail($dto->transactionId)
            : new Transaction;

        $transaction->fill([
            'user_id' => $dto->userId,
            'category_id' => $category->id,
            'client_id' => null,
            'invoice_id' => null,
            'date' => $dto->date,
            'amount' => $dto->amount,
            'currency' => 'RSD',
            'title' => $dto->title,
            'note' => $dto->note,
        ]);

        $transaction->save();

        return $transaction;
    }
}
