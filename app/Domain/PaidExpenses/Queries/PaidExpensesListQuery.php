<?php

declare(strict_types=1);

namespace App\Domain\PaidExpenses\Queries;

use App\Domain\PaidExpenses\DTO\PaidExpensesFiltersData;
use App\Models\Transaction;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;

final class PaidExpensesListQuery
{
    /**
     * @return array{transactions: LengthAwarePaginator<int, \App\Models\Transaction>, monthlyTotal: float}
     */
    public function execute(PaidExpensesFiltersData $dto): array
    {
        $query = Transaction::query()
            ->with(['category.type'])
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

        $monthlyTotal = (float) ((clone $query)->sum('amount'));
        $transactions = $query->orderByDesc('date')->orderByDesc('id')->paginate(20);

        return [
            'transactions' => $transactions,
            'monthlyTotal' => $monthlyTotal,
        ];
    }

    public function findExpenseTransaction(int $transactionId): Transaction
    {
        return Transaction::query()
            ->whereHas('category.type', fn (Builder $typeQuery) => $typeQuery->where('key', 'expense'))
            ->findOrFail($transactionId);
    }
}
