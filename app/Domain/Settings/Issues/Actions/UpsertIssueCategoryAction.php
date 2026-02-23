<?php

declare(strict_types=1);

namespace App\Domain\Settings\Issues\Actions;

use App\Domain\Settings\Issues\DTO\UpsertIssueCategoryData;
use App\Models\IssueCategory;

final class UpsertIssueCategoryAction
{
    public function execute(UpsertIssueCategoryData $dto): IssueCategory
    {
        $category = $dto->categoryId ? IssueCategory::query()->findOrFail($dto->categoryId) : new IssueCategory;

        $category->fill([
            'name' => $dto->name,
            'is_active' => $dto->isActive,
        ]);

        $category->save();

        return $category;
    }
}
