<?php

declare(strict_types=1);

namespace App\Domain\Settings\Security\DTO;

final class UpdatePasswordData
{
    public function __construct(
        public readonly int $userId,
        public readonly string $password,
    ) {}

    public static function fromArray(array $data): self
    {
        return new self((int) $data['user_id'], (string) $data['password']);
    }
}
