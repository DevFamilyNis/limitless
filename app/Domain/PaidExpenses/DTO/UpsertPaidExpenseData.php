<?php

declare(strict_types=1);

namespace App\Domain\PaidExpenses\DTO;

final class UpsertPaidExpenseData
{
    public function __construct(
        public readonly int $userId,
        public readonly ?int $transactionId,
        public readonly int $categoryId,
        public readonly string $date,
        public readonly float $amount,
        public readonly string $title,
        public readonly ?string $note,
    ) {}

    public static function fromArray(array $data): self
    {
        $note = trim((string) ($data['note'] ?? ''));

        return new self(
            userId: (int) $data['user_id'],
            transactionId: isset($data['transaction_id']) ? (int) $data['transaction_id'] : null,
            categoryId: (int) $data['category_id'],
            date: (string) $data['date'],
            amount: (float) $data['amount'],
            title: trim((string) $data['title']),
            note: $note !== '' ? $note : null,
        );
    }
}
