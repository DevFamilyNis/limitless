<?php

declare(strict_types=1);

namespace App\Domain\Transactions\Actions;

use App\Models\Category;
use App\Models\CategoryType;

final class ResolveInvoiceIncomeCategoryAction
{
    public function execute(int $userId): Category
    {
        $incomeCategoryTypeId = CategoryType::query()
            ->where('key', 'income')
            ->value('id');

        if ($incomeCategoryTypeId === null) {
            abort(422, 'Income category type missing.');
        }

        $existingCategory = Category::query()
            ->where('category_type_id', $incomeCategoryTypeId)
            ->where('name', 'Faktura')
            ->first();

        if ($existingCategory) {
            return $existingCategory;
        }

        return Category::query()->create([
            'user_id' => $userId,
            'category_type_id' => $incomeCategoryTypeId,
            'name' => 'Faktura',
        ]);
    }
}
