<?php

declare(strict_types=1);

namespace App\Domain\Transactions\Actions;

use App\Domain\Transactions\DTO\UpsertTransactionData;
use App\Models\Category;
use App\Models\Client;
use App\Models\Invoice;
use App\Models\Transaction;

final class UpsertTransactionAction
{
    public function execute(UpsertTransactionData $dto): Transaction
    {
        $category = Category::query()
            ->findOrFail($dto->categoryId);

        $clientId = null;
        if ($dto->clientId !== null) {
            $clientId = Client::query()
                ->findOrFail($dto->clientId)
                ->id;
        }

        $invoiceId = null;
        if ($dto->documentType === 'invoice') {
            $invoiceId = Invoice::query()
                ->findOrFail((int) $dto->invoiceId)
                ->id;
        }

        $transaction = $dto->transactionId
            ? Transaction::query()->findOrFail($dto->transactionId)
            : new Transaction;

        $transaction->fill([
            'user_id' => $dto->userId,
            'category_id' => $category->id,
            'client_id' => $clientId,
            'invoice_id' => $invoiceId,
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
