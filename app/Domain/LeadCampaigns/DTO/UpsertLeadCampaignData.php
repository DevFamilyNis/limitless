<?php

declare(strict_types=1);

namespace App\Domain\LeadCampaigns\DTO;

final class UpsertLeadCampaignData
{
    public function __construct(
        public readonly ?int $campaignId,
        public readonly string $name,
        public readonly ?string $description,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            campaignId: isset($data['campaign_id']) ? (int) $data['campaign_id'] : null,
            name: trim((string) $data['name']),
            description: self::nullableString($data['description'] ?? null),
        );
    }

    private static function nullableString(mixed $value): ?string
    {
        $result = trim((string) $value);

        return $result !== '' ? $result : null;
    }
}
