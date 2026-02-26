<?php

declare(strict_types=1);

namespace App\Domain\MonthlyExpenses\DTO;

final class MonthlyExpenseItemsFiltersData
{
    public function __construct(
        public readonly int $userId,
        public readonly ?string $search,
    ) {}

    public static function fromArray(array $data): self
    {
        $search = trim((string) ($data['search'] ?? ''));

        return new self(
            userId: (int) $data['user_id'],
            search: $search !== '' ? $search : null,
        );
    }
}
