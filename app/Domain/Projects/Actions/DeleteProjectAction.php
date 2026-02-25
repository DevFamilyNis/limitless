<?php

declare(strict_types=1);

namespace App\Domain\Projects\Actions;

use App\Domain\Projects\DTO\DeleteProjectData;
use App\Models\Project;

final class DeleteProjectAction
{
    public function execute(DeleteProjectData $dto): void
    {
        $project = Project::query()
            ->findOrFail($dto->projectId);

        $project->delete();
    }
}
