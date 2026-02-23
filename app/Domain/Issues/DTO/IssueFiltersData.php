<?php

declare(strict_types=1);

namespace App\Domain\Issues\DTO;

final class IssueFiltersData
{
    public function __construct(
        public readonly int $userId,
        public readonly ?int $projectId,
        public readonly ?string $search,
        public readonly ?int $categoryId,
        public readonly ?int $priorityId,
        public readonly ?int $clientId,
        public readonly ?int $assigneeId,
    ) {}

    public static function fromArray(array $data): self
    {
        $search = trim((string) ($data['search'] ?? ''));

        return new self(
            userId: (int) $data['user_id'],
            projectId: isset($data['project_id']) ? (int) $data['project_id'] : null,
            search: $search !== '' ? $search : null,
            categoryId: isset($data['category_id']) ? (int) $data['category_id'] : null,
            priorityId: isset($data['priority_id']) ? (int) $data['priority_id'] : null,
            clientId: isset($data['client_id']) ? (int) $data['client_id'] : null,
            assigneeId: isset($data['assignee_id']) ? (int) $data['assignee_id'] : null,
        );
    }
}
