<?php

declare(strict_types=1);

namespace App\Domain\Dashboard\Queries;

use App\Models\Transaction;

final class YearlyCashflowQuery
{
    private const MONTH_LABELS = [
        1 => 'Jan', 2 => 'Feb', 3 => 'Mar', 4 => 'Apr',
        5 => 'Maj', 6 => 'Jun', 7 => 'Jul', 8 => 'Avg',
        9 => 'Sep', 10 => 'Okt', 11 => 'Nov', 12 => 'Dec',
    ];

    /**
     * Returns calendar years that have at least one transaction, newest first.
     * Uses PHP-level extraction to remain database-agnostic (SQLite + MySQL).
     *
     * @return array<int, int>
     */
    public function availableYears(): array
    {
        return Transaction::query()
            ->get(['date'])
            ->map(fn (Transaction $t) => (int) $t->date->year)
            ->unique()
            ->sortDesc()
            ->values()
            ->toArray();
    }

    /**
     * Returns full 12-month cashflow breakdown for the given year.
     *
     * @return array{
     *   months: array<int, array{month:int,label:string,income:float,expense:float,net:float}>,
     *   totals: array{income:float,expense:float,net:float},
     *   signal: array{status:string,label:string,recommended_max_investment:float,reason:string}
     * }
     */
    public function execute(int $year): array
    {
        // Load all transactions for the year with category type — database-agnostic approach
        $transactions = Transaction::query()
            ->with('category.type')
            ->whereYear('date', $year)
            ->get();

        $months = [];

        for ($m = 1; $m <= 12; $m++) {
            $monthTxs = $transactions->filter(fn (Transaction $t) => $t->date->month === $m);

            $income = (float) $monthTxs
                ->filter(fn (Transaction $t) => $t->category?->type?->key === 'income')
                ->sum('amount');

            $expense = (float) $monthTxs
                ->filter(fn (Transaction $t) => $t->category?->type?->key === 'expense')
                ->sum('amount');

            $months[] = [
                'month' => $m,
                'label' => self::MONTH_LABELS[$m],
                'income' => $income,
                'expense' => $expense,
                'net' => $income - $expense,
            ];
        }

        $totalIncome = array_sum(array_column($months, 'income'));
        $totalExpense = array_sum(array_column($months, 'expense'));

        return [
            'months' => $months,
            'totals' => [
                'income' => $totalIncome,
                'expense' => $totalExpense,
                'net' => $totalIncome - $totalExpense,
            ],
            'signal' => $this->calculateSignal($months),
        ];
    }

    /**
     * @param  array<int, array{income:float,expense:float,net:float}>  $months
     * @return array{status:string,label:string,recommended_max_investment:float,reason:string}
     */
    private function calculateSignal(array $months): array
    {
        $monthsWithData = array_values(
            array_filter($months, fn (array $m) => $m['income'] > 0 || $m['expense'] > 0)
        );

        if ($monthsWithData === []) {
            return [
                'status' => 'unsafe',
                'label' => 'Nije preporučeno',
                'recommended_max_investment' => 0.0,
                'reason' => 'Nema evidentiranih transakcija za izabranu godinu.',
            ];
        }

        $totalNet = array_sum(array_column($months, 'net'));
        $positiveMonths = count(array_filter($months, fn (array $m) => $m['net'] > 0));
        $count = count($monthsWithData);

        $avgIncome = array_sum(array_column($monthsWithData, 'income')) / $count;
        $avgExpense = array_sum(array_column($monthsWithData, 'expense')) / $count;
        $coverage = $avgExpense > 0 ? $avgIncome / $avgExpense : 0.0;

        // Trend of last 3 months with data
        $last3 = array_slice($monthsWithData, -3);
        $isDownward = count($last3) === 3
            && $last3[2]['net'] < $last3[1]['net']
            && $last3[1]['net'] < $last3[0]['net'];

        $positiveNets = array_filter(array_column($months, 'net'), fn (float $n) => $n > 0);
        $avgPositiveNet = $positiveNets !== []
            ? array_sum($positiveNets) / count($positiveNets)
            : 0.0;

        // SAFE: net positive, 8+ positive months, income ≥ 20% above expense, no downward trend
        if ($totalNet > 0 && $positiveMonths >= 8 && $coverage >= 1.2 && ! $isDownward) {
            $pct = round(($coverage - 1) * 100);

            return [
                'status' => 'safe',
                'label' => 'Moguća investicija',
                'recommended_max_investment' => round($avgPositiveNet * 0.25, 2),
                'reason' => "Stabilna profitabilnost: {$positiveMonths}/12 meseci pozitivno, prosečan prihod je {$pct}% veći od prosečnog rashoda.",
            ];
        }

        // UNSAFE: negative annual net or clear downward trend in last 3 months
        if ($totalNet <= 0 || $isDownward) {
            $reason = $totalNet <= 0
                ? 'Negativan godišnji neto rezultat — rashodi premašuju prihode.'
                : 'Negativan trend neto rezultata u poslednja 3 meseca sa podacima.';

            return [
                'status' => 'unsafe',
                'label' => 'Nije preporučeno',
                'recommended_max_investment' => 0.0,
                'reason' => $reason,
            ];
        }

        // CAUTION: positive but unstable
        return [
            'status' => 'caution',
            'label' => 'Oprez',
            'recommended_max_investment' => round($avgPositiveNet * 0.10, 2),
            'reason' => "Pozitivan ali nestabilan novčani tok: {$positiveMonths}/12 meseci pozitivno. Preporučuje se oprez pre investiranja.",
        ];
    }
}
