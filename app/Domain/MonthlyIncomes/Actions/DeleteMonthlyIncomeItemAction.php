<?php

declare(strict_types=1);

namespace App\Domain\MonthlyIncomes\Actions;

use App\Domain\MonthlyIncomes\DTO\DeleteMonthlyIncomeItemData;
use App\Models\MonthlyIncomeItem;

final class DeleteMonthlyIncomeItemAction
{
    public function execute(DeleteMonthlyIncomeItemData $dto): void
    {
        $item = MonthlyIncomeItem::query()
            ->where('user_id', $dto->userId)
            ->findOrFail($dto->itemId);

        $item->delete();
    }
}
