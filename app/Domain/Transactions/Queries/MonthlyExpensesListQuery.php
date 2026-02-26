<?php

declare(strict_types=1);

namespace App\Domain\Transactions\Queries;

use App\Domain\Transactions\DTO\MonthlyExpensesFiltersData;
use App\Models\Transaction;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;

final class MonthlyExpensesListQuery
{
    /**
     * @return array{expenses: LengthAwarePaginator<int, \App\Models\Transaction>, totalAmount: float}
     */
    public function execute(MonthlyExpensesFiltersData $dto): array
    {
        $query = Transaction::query()
            ->with('category.type')
            ->where('user_id', $dto->userId)
            ->whereYear('date', $dto->year)
            ->whereMonth('date', $dto->month)
            ->whereHas('category.type', fn (Builder $typeQuery) => $typeQuery->where('key', 'expense'))
            ->when($dto->search !== null, function (Builder $query) use ($dto): void {
                $query->where(function (Builder $innerQuery) use ($dto): void {
                    $innerQuery
                        ->where('title', 'like', '%'.$dto->search.'%')
                        ->orWhere('note', 'like', '%'.$dto->search.'%')
                        ->orWhere('amount', 'like', '%'.$dto->search.'%')
                        ->orWhereHas('category', fn (Builder $categoryQuery) => $categoryQuery->where('name', 'like', '%'.$dto->search.'%'));
                });
            });

        $totalAmount = (float) (clone $query)->sum('amount');

        $expenses = $query
            ->orderByDesc('date')
            ->orderByDesc('id')
            ->paginate(10);

        return [
            'expenses' => $expenses,
            'totalAmount' => $totalAmount,
        ];
    }
}
