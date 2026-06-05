<?php

declare(strict_types=1);

namespace App\Domain\Leads\DTO;

final class LeadFiltersData
{
    public function __construct(
        public readonly int $campaignId,
        public readonly ?string $search,
        public readonly ?string $statusKey,
    ) {}

    public static function fromArray(array $data): self
    {
        $search = trim((string) ($data['search'] ?? ''));
        $statusKey = trim((string) ($data['status_key'] ?? ''));

        return new self(
            campaignId: (int) $data['campaign_id'],
            search: $search !== '' ? $search : null,
            statusKey: $statusKey !== '' && $statusKey !== 'all' ? $statusKey : null,
        );
    }
}
