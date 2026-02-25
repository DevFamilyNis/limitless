<?php

declare(strict_types=1);

namespace App\Domain\Issues\Queries;

use App\Domain\Issues\DTO\IssueFiltersData;
use App\Models\Issue;
use Illuminate\Database\Eloquent\Builder;

final class IssueFilteredListQuery
{
    public function execute(IssueFiltersData $dto): Builder
    {
        return Issue::query()
            ->when($dto->projectId !== null, fn ($query) => $query->where('project_id', $dto->projectId))
            ->when($dto->search !== null, function ($query) use ($dto): void {
                $query->where(function ($innerQuery) use ($dto): void {
                    $innerQuery
                        ->where('title', 'like', '%'.$dto->search.'%')
                        ->orWhere('description', 'like', '%'.$dto->search.'%');
                });
            })
            ->when($dto->categoryId !== null, fn ($query) => $query->where('category_id', $dto->categoryId))
            ->when($dto->priorityId !== null, fn ($query) => $query->where('priority_id', $dto->priorityId))
            ->when($dto->clientId !== null, fn ($query) => $query->where('client_id', $dto->clientId))
            ->when($dto->assigneeId !== null, fn ($query) => $query->where('assignee_id', $dto->assigneeId));
    }
}
