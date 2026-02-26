<?php

declare(strict_types=1);

namespace App\Domain\MonthlyExpenses\Queries;

use App\Domain\MonthlyExpenses\DTO\MonthlyExpenseItemsFiltersData;
use App\Models\MonthlyExpenseItem;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;

final class MonthlyExpenseItemsListQuery
{
    /**
     * @return array{items: LengthAwarePaginator<int, \App\Models\MonthlyExpenseItem>, monthlyTotal: float}
     */
    public function execute(MonthlyExpenseItemsFiltersData $dto): array
    {
        $query = MonthlyExpenseItem::query()
            ->with('billingPeriod')
            ->where('user_id', $dto->userId)
            ->when($dto->search !== null, function (Builder $query) use ($dto): void {
                $query->where(function (Builder $innerQuery) use ($dto): void {
                    $innerQuery
                        ->where('title', 'like', '%'.$dto->search.'%')
                        ->orWhere('note', 'like', '%'.$dto->search.'%')
                        ->orWhere('amount', 'like', '%'.$dto->search.'%')
                        ->orWhereHas('billingPeriod', fn (Builder $periodQuery) => $periodQuery->where('name', 'like', '%'.$dto->search.'%'));
                });
            });

        $monthlyTotal = (float) ((clone $query)
            ->join('billing_periods', 'billing_periods.id', '=', 'monthly_expense_items.billing_period_id')
            ->selectRaw("
                SUM(
                    CASE billing_periods.key
                        WHEN 'yearly' THEN monthly_expense_items.amount / 12
                        ELSE monthly_expense_items.amount
                    END
                ) as monthly_total
            ")
            ->value('monthly_total') ?? 0);

        $items = $query
            ->orderBy('title')
            ->paginate(20);

        return [
            'items' => $items,
            'monthlyTotal' => $monthlyTotal,
        ];
    }
}
