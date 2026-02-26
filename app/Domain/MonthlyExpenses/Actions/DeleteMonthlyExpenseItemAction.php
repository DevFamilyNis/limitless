<?php

declare(strict_types=1);

namespace App\Domain\MonthlyExpenses\Actions;

use App\Domain\MonthlyExpenses\DTO\DeleteMonthlyExpenseItemData;
use App\Models\MonthlyExpenseItem;

final class DeleteMonthlyExpenseItemAction
{
    public function execute(DeleteMonthlyExpenseItemData $dto): void
    {
        $item = MonthlyExpenseItem::query()
            ->where('user_id', $dto->userId)
            ->findOrFail($dto->itemId);

        $item->delete();
    }
}
