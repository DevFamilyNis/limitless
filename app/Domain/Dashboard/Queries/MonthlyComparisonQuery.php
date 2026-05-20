<?php

declare(strict_types=1);

namespace App\Domain\Dashboard\Queries;

use App\Models\Transaction;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

final class MonthlyComparisonQuery
{
    /**
     * Compares current month against the YTD monthly average for this calendar year.
     */
    public function execute(): array
    {
        $now = Carbon::now();
        $curYear = (int) $now->year;
        $curMonth = (int) $now->month;

        // ── Current month ────────────────────────────────────────────────────
        $curIncome = $this->monthSum('income', $curYear, $curMonth);
        $curExpense = $this->monthSum('expense', $curYear, $curMonth);
        $curNet = $curIncome - $curExpense;

        // ── YTD aggregates (January → current month inclusive) ───────────────
        $ytdRows = Transaction::query()
            ->join('categories', 'categories.id', '=', 'transactions.category_id')
            ->join('category_types', 'category_types.id', '=', 'categories.category_type_id')
            ->whereYear('transactions.date', $curYear)
            ->whereMonth('transactions.date', '<=', $curMonth)
            ->selectRaw('category_types.key as type_key, SUM(transactions.amount) as total')
            ->groupBy('category_types.key')
            ->get()
            ->pluck('total', 'type_key');

        $ytdIncome = (float) ($ytdRows['income'] ?? 0);
        $ytdExpense = (float) ($ytdRows['expense'] ?? 0);

        // Average over months elapsed (including current month)
        $avgIncome = $ytdIncome / $curMonth;
        $avgExpense = $ytdExpense / $curMonth;
        $avgNet = ($ytdIncome - $ytdExpense) / $curMonth;

        // ── Top expense category vs YTD monthly average ───────────────────────
        $curByCat = $this->expenseByCategory($curYear, $curMonth);
        $ytdByCat = $this->ytdExpenseByCategory($curYear, $curMonth);

        $topCategory = null;
        $topGrowth = 0.0;
        foreach ($curByCat as $name => $curAmt) {
            $catAvg = ($ytdByCat[$name] ?? 0.0) / $curMonth;
            $growth = $curAmt - $catAvg;
            if ($growth > $topGrowth) {
                $topGrowth = $growth;
                $topCategory = $name;
            }
        }

        // ── % vs YTD average ─────────────────────────────────────────────────
        $incomePct = $this->pct($curIncome, $avgIncome);
        $expensePct = $this->pct($curExpense, $avgExpense);
        $netPct = $this->pct($curNet, $avgNet);
        $margin = $curIncome > 0 ? round(($curNet / $curIncome) * 100, 1) : null;

        return [
            'period' => $now->translatedFormat('F Y'),
            'summary' => $this->summary($incomePct, $curNet, $avgNet, $expensePct, $topCategory, $curIncome),
            'metrics' => [
                [
                    'label' => 'Prihodi',
                    'value' => $curIncome,
                    'pct' => $incomePct,
                    'signal' => $this->incomeSignal($incomePct, $curIncome, $avgIncome),
                    'direction' => $this->dir($incomePct),
                    'format' => 'currency',
                ],
                [
                    'label' => 'Neto',
                    'value' => $curNet,
                    'pct' => $netPct,
                    'signal' => $this->netSignal($curNet, $avgNet, $netPct),
                    'direction' => $curNet >= 0 ? ($netPct !== null && $netPct >= 0 ? 'up' : 'neutral') : 'down',
                    'format' => 'currency',
                ],
                [
                    'label' => 'Rashodi',
                    'value' => $curExpense,
                    'pct' => $expensePct,
                    'signal' => $this->expenseSignal($expensePct, $topCategory, $topGrowth),
                    'direction' => $expensePct !== null ? ($expensePct <= 0 ? 'up' : 'down') : 'neutral',
                    'format' => 'currency',
                ],
                [
                    'label' => 'Marža',
                    'value' => $margin,
                    'pct' => null,
                    'signal' => $this->marginSignal($margin),
                    'direction' => $margin === null ? 'neutral' : ($margin >= 30 ? 'up' : ($margin >= 0 ? 'neutral' : 'down')),
                    'format' => 'percent',
                ],
            ],
        ];
    }

    private function monthSum(string $typeKey, int $year, int $month): float
    {
        return (float) Transaction::query()
            ->join('categories', 'categories.id', '=', 'transactions.category_id')
            ->join('category_types', 'category_types.id', '=', 'categories.category_type_id')
            ->where('category_types.key', $typeKey)
            ->whereYear('transactions.date', $year)
            ->whereMonth('transactions.date', $month)
            ->sum('transactions.amount');
    }

    /** @return array<string, float> */
    private function expenseByCategory(int $year, int $month): array
    {
        return Transaction::query()
            ->join('categories', 'categories.id', '=', 'transactions.category_id')
            ->join('category_types', 'category_types.id', '=', 'categories.category_type_id')
            ->where('category_types.key', 'expense')
            ->whereYear('transactions.date', $year)
            ->whereMonth('transactions.date', $month)
            ->select('categories.name', DB::raw('SUM(transactions.amount) as total'))
            ->groupBy('categories.id', 'categories.name')
            ->get()
            ->pluck('total', 'name')
            ->map(fn ($v) => (float) $v)
            ->toArray();
    }

    /** @return array<string, float> YTD totals by category */
    private function ytdExpenseByCategory(int $year, int $curMonth): array
    {
        return Transaction::query()
            ->join('categories', 'categories.id', '=', 'transactions.category_id')
            ->join('category_types', 'category_types.id', '=', 'categories.category_type_id')
            ->where('category_types.key', 'expense')
            ->whereYear('transactions.date', $year)
            ->whereMonth('transactions.date', '<=', $curMonth)
            ->select('categories.name', DB::raw('SUM(transactions.amount) as total'))
            ->groupBy('categories.id', 'categories.name')
            ->get()
            ->pluck('total', 'name')
            ->map(fn ($v) => (float) $v)
            ->toArray();
    }

    private function pct(float $current, float $average): ?float
    {
        // No comparison makes sense when either side is zero
        if ($average == 0 || $current == 0) {
            return null;
        }

        return round((($current - $average) / $average) * 100, 1);
    }

    private function dir(?float $pct): string
    {
        if ($pct === null) {
            return 'neutral';
        }

        return $pct > 0 ? 'up' : ($pct < 0 ? 'down' : 'neutral');
    }

    private function incomeSignal(?float $pct, float $cur, float $avg): string
    {
        if ($cur == 0 && $avg == 0) {
            return 'Nema prihoda evidentirano ove godine';
        }
        if ($avg == 0) {
            return 'Prvi prihodi ove godine';
        }
        if ($pct === null) {
            return 'Nema proseka za poređenje';
        }
        if ($pct >= 20) {
            return 'Iznad proseka ove godine — odličan mesec';
        }
        if ($pct >= 5) {
            return 'Iznad godišnjeg proseka';
        }
        if ($pct >= -5) {
            return 'U okviru godišnjeg proseka';
        }
        if ($pct >= -20) {
            return 'Ispod godišnjeg proseka';
        }

        return 'Značajno ispod proseka — proveri fakturisanje';
    }

    private function netSignal(float $cur, float $avg, ?float $pct): string
    {
        if ($cur > 0 && $avg > 0 && $pct !== null && $pct >= 10) {
            return 'Iznad prosečnog neta ove godine';
        }
        if ($cur > 0 && $avg <= 0) {
            return 'Profitabilan mesec u godini sa negativnim prosekom';
        }
        if ($cur > 0) {
            return 'Pozitivan neto ovog meseca';
        }
        if ($cur == 0) {
            return 'Nulti rezultat — prihodi = rashodi';
        }
        if ($avg < 0) {
            return 'Negativan neto, u skladu sa godišnjim prosekom';
        }

        return 'Ispod godišnjeg proseka neta';
    }

    private function expenseSignal(?float $pct, ?string $topCategory, float $topGrowth): string
    {
        if ($pct === null) {
            return 'Nema proseka za poređenje';
        }
        if ($pct <= -10) {
            return 'Rashodi ispod godišnjeg proseka — dobra kontrola';
        }
        if ($pct <= 5) {
            return 'Rashodi u okviru godišnjeg proseka';
        }
        if ($topCategory !== null && $topGrowth > 0) {
            return "Rashod za \"{$topCategory}\" iznad proseka — proveri";
        }

        return 'Rashodi iznad godišnjeg proseka';
    }

    private function marginSignal(?float $margin): string
    {
        if ($margin === null) {
            return 'Nema prihoda za računanje';
        }
        if ($margin >= 60) {
            return 'Odlična profitabilnost';
        }
        if ($margin >= 40) {
            return 'Dobra profitna marža';
        }
        if ($margin >= 20) {
            return 'Umerena marža';
        }
        if ($margin >= 0) {
            return 'Niska marža — optimizuj troškove';
        }

        return 'Negativna marža ovog meseca';
    }

    private function summary(?float $incomePct, float $curNet, float $avgNet, ?float $expensePct, ?string $topCategory, float $curIncome): string
    {
        if ($curIncome == 0) {
            return 'Nema evidentiranih prihoda za ovaj mesec. Proveri da li su sve fakture unesene.';
        }

        $parts = [];

        if ($incomePct !== null && $incomePct >= 10) {
            $parts[] = 'Prihodi su iznad godišnjeg proseka';
        } elseif ($incomePct !== null && $incomePct <= -10) {
            $parts[] = 'Prihodi su ispod godišnjeg proseka';
        } else {
            $parts[] = 'Prihodi su u okviru godišnjeg proseka';
        }

        if ($curNet > 0 && $avgNet > 0) {
            $parts[] = 'neto je pozitivan';
        } elseif ($curNet > 0) {
            $parts[] = 'neto je pozitivan ovaj mesec';
        } else {
            $parts[] = 'ali neto je negativan';
        }

        if ($topCategory !== null && $expensePct !== null && $expensePct > 10) {
            return implode(', ', $parts).sprintf(', ali rashodi za "%s" su iznad proseka — proveri.', $topCategory);
        }

        return implode(', ', $parts).'.';
    }
}
