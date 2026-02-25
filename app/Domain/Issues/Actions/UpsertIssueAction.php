<?php

declare(strict_types=1);

namespace App\Domain\Issues\Actions;

use App\Domain\Issues\DTO\UpsertIssueData;
use App\Models\Issue;
use App\Models\IssueStatus;
use App\Models\Project;
use Illuminate\Auth\Access\AuthorizationException;

final class UpsertIssueAction
{
    public function execute(UpsertIssueData $dto): Issue
    {
        $authorId = auth()->id();

        if (! is_int($authorId)) {
            throw new AuthorizationException('Nije moguće odrediti autora issue-a.');
        }

        $project = Project::query()
            ->findOrFail($dto->projectId);

        $issue = $dto->issueId
            ? Issue::query()
                ->findOrFail($dto->issueId)
            : new Issue;

        $status = IssueStatus::query()->findOrFail($dto->statusId);

        $issue->fill([
            'project_id' => $project->id,
            'client_id' => $dto->clientId,
            'client_contact_id' => $dto->clientContactId,
            'status_id' => $dto->statusId,
            'priority_id' => $dto->priorityId,
            'category_id' => $dto->categoryId,
            'author_id' => $issue->exists ? $issue->author_id : $authorId,
            'assignee_id' => $dto->assigneeId,
            'title' => $dto->title,
            'description' => $dto->description,
            'due_date' => $dto->dueDate,
            'completed_at' => $status->key === 'done' ? now() : null,
        ]);

        $issue->save();

        return $issue;
    }
}
