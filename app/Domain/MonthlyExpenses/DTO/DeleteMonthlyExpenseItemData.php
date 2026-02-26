<?php

declare(strict_types=1);

namespace App\Domain\MonthlyExpenses\DTO;

final class DeleteMonthlyExpenseItemData
{
    public function __construct(
        public readonly int $userId,
        public readonly int $itemId,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            userId: (int) $data['user_id'],
            itemId: (int) $data['item_id'],
        );
    }
}
