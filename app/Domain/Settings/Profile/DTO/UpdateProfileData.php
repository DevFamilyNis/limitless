<?php

declare(strict_types=1);

namespace App\Domain\Settings\Profile\DTO;

final class UpdateProfileData
{
    public function __construct(
        public readonly int $userId,
        public readonly string $name,
        public readonly string $email,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self(
            userId: (int) $data['user_id'],
            name: trim((string) $data['name']),
            email: trim((string) $data['email']),
        );
    }
}
