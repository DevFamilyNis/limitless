<?php

declare(strict_types=1);

namespace App\Domain\Issues\DTO;

final class UpsertIssueData
{
    public function __construct(
        public readonly int $userId,
        public readonly ?int $issueId,
        public readonly int $projectId,
        public readonly ?int $clientId,
        public readonly ?int $clientContactId,
        public readonly int $statusId,
        public readonly int $priorityId,
        public readonly int $categoryId,
        public readonly ?int $assigneeId,
        public readonly string $title,
        public readonly ?string $description,
        public readonly ?string $dueDate,
    ) {}

    public static function fromArray(array $data): self
    {
        $description = trim((string) ($data['description'] ?? ''));

        return new self(
            userId: (int) $data['user_id'],
            issueId: isset($data['issue_id']) ? (int) $data['issue_id'] : null,
            projectId: (int) $data['project_id'],
            clientId: isset($data['client_id']) ? (int) $data['client_id'] : null,
            clientContactId: isset($data['client_contact_id']) ? (int) $data['client_contact_id'] : null,
            statusId: (int) $data['status_id'],
            priorityId: (int) $data['priority_id'],
            categoryId: (int) $data['category_id'],
            assigneeId: isset($data['assignee_id']) ? (int) $data['assignee_id'] : null,
            title: trim((string) $data['title']),
            description: $description !== '' ? $description : null,
            dueDate: isset($data['due_date']) && $data['due_date'] !== '' ? (string) $data['due_date'] : null,
        );
    }
}
