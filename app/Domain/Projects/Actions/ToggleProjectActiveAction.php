<?php

declare(strict_types=1);

namespace App\Domain\Projects\Actions;

use App\Domain\Projects\DTO\ToggleProjectActiveData;
use App\Models\Project;

final class ToggleProjectActiveAction
{
    public function execute(ToggleProjectActiveData $dto): Project
    {
        $project = Project::query()
            ->where('user_id', $dto->userId)
            ->findOrFail($dto->projectId);

        $project->update([
            'is_active' => ! $project->is_active,
        ]);

        return $project;
    }
}
