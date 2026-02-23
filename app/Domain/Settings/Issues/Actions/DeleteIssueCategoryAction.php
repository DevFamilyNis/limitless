<?php

declare(strict_types=1);

namespace App\Domain\Settings\Issues\Actions;

use App\Domain\Settings\Issues\DTO\DeleteIssueCategoryData;
use App\Models\IssueCategory;

final class DeleteIssueCategoryAction
{
    public function execute(DeleteIssueCategoryData $dto): void
    {
        IssueCategory::query()->findOrFail($dto->categoryId)->delete();
    }
}
