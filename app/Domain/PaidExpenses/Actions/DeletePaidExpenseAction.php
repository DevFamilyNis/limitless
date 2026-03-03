<?php

declare(strict_types=1);

namespace App\Domain\PaidExpenses\Actions;

use App\Domain\PaidExpenses\DTO\DeletePaidExpenseData;
use App\Models\Transaction;

final class DeletePaidExpenseAction
{
    public function execute(DeletePaidExpenseData $dto): void
    {
        $transaction = Transaction::query()->findOrFail($dto->transactionId);
        $transaction->delete();
    }
}
