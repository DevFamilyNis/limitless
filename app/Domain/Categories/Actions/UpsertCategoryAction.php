<?php

declare(strict_types=1);

namespace App\Domain\Categories\Actions;

use App\Domain\Categories\DTO\UpsertCategoryData;
use App\Models\Category;

final class UpsertCategoryAction
{
    public function execute(UpsertCategoryData $dto): Category
    {
        $category = $dto->categoryId
            ? Category::query()
                ->findOrFail($dto->categoryId)
            : new Category;

        $category->fill([
            'user_id' => $dto->userId,
            'category_type_id' => $dto->categoryTypeId,
            'name' => $dto->name,
        ]);

        $category->save();

        return $category;
    }
}
