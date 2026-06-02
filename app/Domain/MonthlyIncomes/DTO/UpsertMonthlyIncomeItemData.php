<?php

declare(strict_types=1);

namespace App\Domain\MonthlyIncomes\DTO;

final class UpsertMonthlyIncomeItemData
{
    public function __construct(
        public readonly int $userId,
        public readonly ?int $itemId,
        public readonly int $billingPeriodId,
        public readonly string $name,
        public readonly float $price,
        public readonly ?string $description,
    ) {}

    public static function fromArray(array $data): self
    {
        $description = trim((string) ($data['description'] ?? ''));

        return new self(
            userId: (int) $data['user_id'],
            itemId: isset($data['item_id']) ? (int) $data['item_id'] : null,
            billingPeriodId: (int) $data['billing_period_id'],
            name: trim((string) $data['name']),
            price: (float) $data['price'],
            description: $description !== '' ? $description : null,
        );
    }
}
