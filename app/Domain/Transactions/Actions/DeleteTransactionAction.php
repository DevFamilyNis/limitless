<?php

declare(strict_types=1);

namespace App\Domain\Transactions\Actions;

use App\Domain\Transactions\DTO\DeleteTransactionData;
use App\Models\Transaction;

final class DeleteTransactionAction
{
    public function execute(DeleteTransactionData $dto): void
    {
        $transaction = Transaction::query()
            ->where('user_id', $dto->userId)
            ->findOrFail($dto->transactionId);

        $transaction->delete();
    }
}
