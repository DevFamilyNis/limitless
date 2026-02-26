<?php

declare(strict_types=1);

namespace App\Domain\MonthlyExpenses\DTO;

final class UpsertMonthlyExpenseItemData
{
    public function __construct(
        public readonly int $userId,
        public readonly ?int $itemId,
        public readonly int $billingPeriodId,
        public readonly string $title,
        public readonly float $amount,
        public readonly ?string $note,
    ) {}

    public static function fromArray(array $data): self
    {
        $note = trim((string) ($data['note'] ?? ''));

        return new self(
            userId: (int) $data['user_id'],
            itemId: isset($data['item_id']) ? (int) $data['item_id'] : null,
            billingPeriodId: (int) $data['billing_period_id'],
            title: trim((string) $data['title']),
            amount: (float) $data['amount'],
            note: $note !== '' ? $note : null,
        );
    }
}
