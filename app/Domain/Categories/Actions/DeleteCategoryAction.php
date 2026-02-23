<?php

declare(strict_types=1);

namespace App\Domain\Categories\Actions;

use App\Domain\Categories\DTO\DeleteCategoryData;

final class DeleteCategoryAction
{
    public function execute(DeleteCategoryData $dto): void
    {
        $category = \App\Models\Category::query()
            ->where('user_id', $dto->userId)
            ->findOrFail($dto->categoryId);

        $category->delete();
    }
}
