<?php

declare(strict_types=1);

namespace App\Domain\WorkSessions\DTO;

use Carbon\CarbonInterface;

final readonly class StartWorkSessionData
{
    public function __construct(
        public int $userId,
        public CarbonInterface $workDate,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            userId: (int) $data['user_id'],
            workDate: isset($data['work_date']) ? Carbon::parse($data['work_date']) : today(),
        );
    }
}
