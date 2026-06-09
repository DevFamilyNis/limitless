<?php

declare(strict_types=1);

namespace App\Domain\WorkSessions\DTO;

final readonly class FinishWorkSessionData
{
    public function __construct(
        public int $userId,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            userId: (int) $data['user_id'],
        );
    }
}
