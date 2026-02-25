<?php

declare(strict_types=1);

namespace App\Domain\Projects\DTO;

final class UpsertProjectData
{
    public function __construct(
        public readonly int $userId,
        public readonly ?int $projectId,
        public readonly string $code,
        public readonly string $name,
        public readonly ?string $description,
        public readonly ?string $projectColor,
    ) {}

    public static function fromArray(array $data): self
    {
        $description = trim((string) ($data['description'] ?? ''));

        return new self(
            userId: (int) $data['user_id'],
            projectId: isset($data['project_id']) ? (int) $data['project_id'] : null,
            code: strtoupper(trim((string) $data['code'])),
            name: trim((string) $data['name']),
            description: $description !== '' ? $description : null,
            projectColor: isset($data['project_color']) && $data['project_color'] !== '' ? (string) $data['project_color'] : null,
        );
    }
}
