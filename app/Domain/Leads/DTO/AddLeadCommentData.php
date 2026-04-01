<?php

declare(strict_types=1);

namespace App\Domain\Leads\DTO;

use Carbon\CarbonImmutable;

final class AddLeadCommentData
{
    public function __construct(
        public readonly int $userId,
        public readonly int $leadId,
        public readonly ?int $leadStatusId,
        public readonly string $eventType,
        public readonly ?string $contactMethod,
        public readonly ?string $outcome,
        public readonly string $body,
        public readonly ?CarbonImmutable $contactedAt,
        public readonly ?CarbonImmutable $respondedAt,
        public readonly ?CarbonImmutable $nextFollowUpAt,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            userId: (int) $data['user_id'],
            leadId: (int) $data['lead_id'],
            leadStatusId: isset($data['lead_status_id']) && $data['lead_status_id'] !== '' ? (int) $data['lead_status_id'] : null,
            eventType: trim((string) $data['event_type']),
            contactMethod: self::nullableString($data['contact_method'] ?? null),
            outcome: self::nullableString($data['outcome'] ?? null),
            body: trim((string) $data['body']),
            contactedAt: self::nullableDateTime($data['contacted_at'] ?? null),
            respondedAt: self::nullableDateTime($data['responded_at'] ?? null),
            nextFollowUpAt: self::nullableDateTime($data['next_follow_up_at'] ?? null),
        );
    }

    private static function nullableString(mixed $value): ?string
    {
        $result = trim((string) $value);

        return $result !== '' ? $result : null;
    }

    private static function nullableDateTime(mixed $value): ?CarbonImmutable
    {
        $string = trim((string) $value);

        return $string !== '' ? CarbonImmutable::parse($string) : null;
    }
}
