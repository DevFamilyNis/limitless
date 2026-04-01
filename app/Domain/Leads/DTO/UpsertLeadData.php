<?php

declare(strict_types=1);

namespace App\Domain\Leads\DTO;

final class UpsertLeadData
{
    public function __construct(
        public readonly int $userId,
        public readonly ?int $leadId,
        public readonly int $leadStatusId,
        public readonly string $companyName,
        public readonly ?string $email,
        public readonly ?string $phone,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            userId: (int) $data['user_id'],
            leadId: isset($data['lead_id']) ? (int) $data['lead_id'] : null,
            leadStatusId: (int) $data['lead_status_id'],
            companyName: trim((string) $data['company_name']),
            email: self::nullableString($data['email'] ?? null),
            phone: self::nullableString($data['phone'] ?? null),
        );
    }

    private static function nullableString(mixed $value): ?string
    {
        $result = trim((string) $value);

        return $result !== '' ? $result : null;
    }
}
