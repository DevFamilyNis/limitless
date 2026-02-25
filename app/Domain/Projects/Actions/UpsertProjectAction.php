<?php

declare(strict_types=1);

namespace App\Domain\Projects\Actions;

use App\Domain\Projects\DTO\UpsertProjectData;
use App\Models\Project;
use App\Support\ProjectColorPalette;

final class UpsertProjectAction
{
    public function execute(UpsertProjectData $dto): Project
    {
        $project = $dto->projectId
            ? Project::query()->findOrFail($dto->projectId)
            : new Project;

        $project->fill([
            'user_id' => $dto->userId,
            'code' => $dto->code,
            'name' => $dto->name,
            'description' => $dto->description,
            'project_color' => $dto->projectColor ?? ProjectColorPalette::suggestedName($dto->code, $dto->name, $project->id),
            'is_active' => $project->exists ? $project->is_active : true,
        ]);

        $project->save();

        return $project;
    }
}
