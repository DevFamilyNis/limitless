<?php

declare(strict_types=1);

namespace App\Domain\WorkSessions\DTO;

final readonly class UpsertWorkSessionUserSettingData
{
    public function __construct(
        public int $userId,
        public bool $reminderEnabled,
        public ?int $reminderDelayMinutes,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            userId: (int) $data['user_id'],
            reminderEnabled: (bool) $data['reminder_enabled'],
            reminderDelayMinutes: isset($data['reminder_delay_minutes']) ? (int) $data['reminder_delay_minutes'] : null,
        );
    }
}
