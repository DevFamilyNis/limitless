<?php

declare(strict_types=1);

namespace App\Domain\WorkSessions\DTO;

final readonly class ForceDeleteWorkSessionData
{
    public function __construct(
        public int $workSessionId,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            workSessionId: (int) $data['work_session_id'],
        );
    }
}
