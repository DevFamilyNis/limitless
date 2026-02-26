<?php

declare(strict_types=1);

namespace App\Domain\Transactions\DTO;

final class DeleteTransactionData
{
    public function __construct(
        public readonly int $userId,
        public readonly int $transactionId,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            userId: (int) $data['user_id'],
            transactionId: (int) $data['transaction_id'],
        );
    }
}
