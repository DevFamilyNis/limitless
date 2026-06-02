<?php

declare(strict_types=1);

namespace App\Domain\MonthlyIncomes\Queries;

use App\Domain\MonthlyIncomes\DTO\MonthlyIncomeItemsFiltersData;
use App\Models\MonthlyIncomeItem;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;

final class MonthlyIncomeItemsListQuery
{
    /**
     * @return array{items: LengthAwarePaginator<int, \App\Models\MonthlyIncomeItem>, monthlyTotal: float}
     */
    public function execute(MonthlyIncomeItemsFiltersData $dto): array
    {
        $query = MonthlyIncomeItem::query()
            ->with('billingPeriod')
            ->where('user_id', $dto->userId)
            ->when($dto->search !== null, function (Builder $query) use ($dto): void {
                $query->where(function (Builder $innerQuery) use ($dto): void {
                    $innerQuery
                        ->where('name', 'like', '%'.$dto->search.'%')
                        ->orWhere('description', 'like', '%'.$dto->search.'%')
                        ->orWhere('price', 'like', '%'.$dto->search.'%')
                        ->orWhereHas('billingPeriod', fn (Builder $periodQuery) => $periodQuery->where('name', 'like', '%'.$dto->search.'%'));
                });
            });

        $monthlyTotal = (float) ((clone $query)
            ->join('billing_periods', 'billing_periods.id', '=', 'monthly_income_items.billing_period_id')
            ->selectRaw("
                SUM(
                    CASE billing_periods.key
                        WHEN 'yearly' THEN monthly_income_items.price / 12
                        ELSE monthly_income_items.price
                    END
                ) as monthly_total
            ")
            ->value('monthly_total') ?? 0);

        $items = $query
            ->orderBy('name')
            ->paginate(20);

        return [
            'items' => $items,
            'monthlyTotal' => $monthlyTotal,
        ];
    }
}
