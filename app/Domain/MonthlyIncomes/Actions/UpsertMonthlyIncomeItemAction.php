<?php

declare(strict_types=1);

namespace App\Domain\MonthlyIncomes\Actions;

use App\Domain\MonthlyIncomes\DTO\UpsertMonthlyIncomeItemData;
use App\Models\BillingPeriod;
use App\Models\MonthlyIncomeItem;

final class UpsertMonthlyIncomeItemAction
{
    public function execute(UpsertMonthlyIncomeItemData $dto): MonthlyIncomeItem
    {
        $billingPeriod = BillingPeriod::query()
            ->whereIn('key', ['monthly', 'yearly'])
            ->findOrFail($dto->billingPeriodId);

        $item = $dto->itemId
            ? MonthlyIncomeItem::query()
                ->where('user_id', $dto->userId)
                ->findOrFail($dto->itemId)
            : new MonthlyIncomeItem;

        $item->fill([
            'user_id' => $dto->userId,
            'billing_period_id' => $billingPeriod->id,
            'name' => $dto->name,
            'price' => $dto->price,
            'description' => $dto->description,
        ]);

        $item->save();

        return $item;
    }
}
