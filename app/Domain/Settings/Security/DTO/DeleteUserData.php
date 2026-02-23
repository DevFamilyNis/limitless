<?php

declare(strict_types=1);

namespace App\Domain\Settings\Security\DTO;

final class DeleteUserData
{
    public function __construct(public readonly int $userId) {}

    public static function fromArray(array $data): self
    {
        return new self((int) $data['user_id']);
    }
}
