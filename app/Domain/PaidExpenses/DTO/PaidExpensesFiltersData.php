<?php

declare(strict_types=1);

namespace App\Domain\PaidExpenses\DTO;

final class PaidExpensesFiltersData
{
    public function __construct(
        public readonly int $month,
        public readonly int $year,
        public readonly ?string $search,
    ) {}

    public static function fromArray(array $data): self
    {
        $search = trim((string) ($data['search'] ?? ''));

        return new self(
            month: (int) $data['month'],
            year: (int) $data['year'],
            search: $search !== '' ? $search : null,
        );
    }
}
