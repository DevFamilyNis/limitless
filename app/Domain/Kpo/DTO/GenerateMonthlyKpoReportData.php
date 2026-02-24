<?php

declare(strict_types=1);

namespace App\Domain\Kpo\DTO;

final class GenerateMonthlyKpoReportData
{
    public function __construct(
        public readonly int $userId,
        public readonly int $year,
        public readonly int $month,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            userId: (int) $data['user_id'],
            year: (int) $data['year'],
            month: (int) $data['month'],
        );
    }
}
