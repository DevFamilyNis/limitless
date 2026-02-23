<?php

declare(strict_types=1);

namespace App\Domain\ClientProjectRates\DTO;

final class ToggleClientProjectRateData
{
    public function __construct(
        public readonly int $userId,
        public readonly int $rateId,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self((int) $data['user_id'], (int) $data['rate_id']);
    }
}
