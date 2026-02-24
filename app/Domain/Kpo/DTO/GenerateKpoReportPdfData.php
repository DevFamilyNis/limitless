<?php

declare(strict_types=1);

namespace App\Domain\Kpo\DTO;

final class GenerateKpoReportPdfData
{
    public function __construct(
        public readonly int $userId,
        public readonly int $kpoReportId,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            userId: (int) $data['user_id'],
            kpoReportId: (int) $data['kpo_report_id'],
        );
    }
}
