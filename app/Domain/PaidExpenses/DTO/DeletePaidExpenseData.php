<?php

declare(strict_types=1);

namespace App\Domain\PaidExpenses\DTO;

final class DeletePaidExpenseData
{
    public function __construct(public readonly int $transactionId) {}

    public static function fromArray(array $data): self
    {
        return new self((int) $data['transaction_id']);
    }
}
