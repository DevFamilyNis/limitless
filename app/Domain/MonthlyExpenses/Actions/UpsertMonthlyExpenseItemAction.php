<?php

declare(strict_types=1);

namespace App\Domain\MonthlyExpenses\Actions;

use App\Domain\MonthlyExpenses\DTO\UpsertMonthlyExpenseItemData;
use App\Models\BillingPeriod;
use App\Models\MonthlyExpenseItem;

final class UpsertMonthlyExpenseItemAction
{
    public function execute(UpsertMonthlyExpenseItemData $dto): MonthlyExpenseItem
    {
        $billingPeriod = BillingPeriod::query()
            ->whereIn('key', ['monthly', 'yearly'])
            ->findOrFail($dto->billingPeriodId);

        $item = $dto->itemId
            ? MonthlyExpenseItem::query()
                ->where('user_id', $dto->userId)
                ->findOrFail($dto->itemId)
            : new MonthlyExpenseItem;

        $item->fill([
            'user_id' => $dto->userId,
            'billing_period_id' => $billingPeriod->id,
            'title' => $dto->title,
            'amount' => $dto->amount,
            'note' => $dto->note,
        ]);

        $item->save();

        return $item;
    }
}
