<?php

declare(strict_types=1);

namespace App\Domain\Leads\DTO;

final class DeleteLeadData
{
    public function __construct(
        public readonly int $userId,
        public readonly int $leadId,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            userId: (int) $data['user_id'],
            leadId: (int) $data['lead_id'],
        );
    }
}
