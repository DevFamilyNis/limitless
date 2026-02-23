<?php

declare(strict_types=1);

namespace App\Domain\Projects\Actions;

use App\Domain\Projects\DTO\UpsertProjectData;
use App\Models\Project;

final class UpsertProjectAction
{
    public function execute(UpsertProjectData $dto): Project
    {
        $project = $dto->projectId
            ? Project::query()->where('user_id', $dto->userId)->findOrFail($dto->projectId)
            : new Project;

        $project->fill([
            'user_id' => $dto->userId,
            'code' => $dto->code,
            'name' => $dto->name,
            'description' => $dto->description,
            'is_active' => $project->exists ? $project->is_active : true,
        ]);

        $project->save();

        return $project;
    }
}
