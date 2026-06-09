<?php

declare(strict_types=1);

namespace App\Domain\WorkSessions\DTO;

use Carbon\Carbon;
use Carbon\CarbonInterface;

final readonly class GenerateWorkSessionReportData
{
    public function __construct(
        public ?int $userId,
        public CarbonInterface $dateFrom,
        public CarbonInterface $dateTo,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            userId: isset($data['user_id']) && $data['user_id'] !== '' ? (int) $data['user_id'] : null,
            dateFrom: Carbon::parse($data['date_from'])->startOfDay(),
            dateTo: Carbon::parse($data['date_to'])->endOfDay(),
        );
    }
}
