<?php

declare(strict_types=1);

namespace App\Livewire\Dashboard;

use App\Domain\Dashboard\Queries\YearlyCashflowQuery;
use Illuminate\Contracts\View\View;
use Livewire\Component;

class CashflowChart extends Component
{
    public int $selectedYear = 0;

    /** @var array<int, int> */
    public array $availableYears = [];

    public function mount(): void
    {
        $this->availableYears = (new YearlyCashflowQuery)->availableYears();
        $this->selectedYear = $this->availableYears[0] ?? now()->year;
    }

    public function updatedSelectedYear(): void
    {
        // Livewire re-renders automatically — signal and chart refresh via render()
    }

    public function render(): View
    {
        $data = [];
        $svg = [];

        if ($this->availableYears !== []) {
            $data = (new YearlyCashflowQuery)->execute($this->selectedYear);
            $svg = $this->buildSvgData($data['months']);
        }

        return view('livewire.dashboard.cashflow-chart', [
            'data' => $data,
            'svg' => $svg,
        ])->layout('layouts.app', [
            'title' => 'Dashboard',
        ]);
    }

    /**
     * Pre-computes all SVG coordinates in PHP so Blade renders pure static SVG.
     * No JavaScript needed for the chart itself.
     *
     * @param  array<int, array{income:float,expense:float,net:float,label:string}>  $months
     * @return array<string, mixed>
     */
    private function buildSvgData(array $months): array
    {
        $vbW = 840;
        $vbH = 160; // aspect ratio 840:160 ≈ 5.25:1 → ~152px tall at 800px width
        $padL = 70;
        $padR = 20;
        $padT = 8;
        $padB = 28;
        $innerW = $vbW - $padL - $padR;
        $innerH = $vbH - $padT - $padB;
        $bottomY = $padT + $innerH;

        $allValues = array_merge(array_column($months, 'income'), array_column($months, 'expense'));
        $rawMax = max(array_merge($allValues, [1.0]));
        $maxVal = $this->niceMax($rawMax);

        $xs = [];
        $yIncome = [];
        $yExpense = [];

        foreach ($months as $i => $m) {
            $xs[$i] = round($padL + ($i / 11) * $innerW, 2);
            $yIncome[$i] = $maxVal > 0 ? round($padT + $innerH - ($m['income'] / $maxVal) * $innerH, 2) : $bottomY;
            $yExpense[$i] = $maxVal > 0 ? round($padT + $innerH - ($m['expense'] / $maxVal) * $innerH, 2) : $bottomY;
        }

        $ptsFn = fn (array $ys) => implode(' ', array_map(fn ($i) => "{$xs[$i]},{$ys[$i]}", range(0, 11)));

        $areaFn = fn (array $ys) => "M {$xs[0]},{$bottomY} ".
            implode(' ', array_map(fn ($i) => "L {$xs[$i]},{$ys[$i]}", range(0, 11))).
            " L {$xs[11]},{$bottomY} Z";

        // Y-axis: 5 labeled grid lines
        $yLabels = [];
        for ($j = 0; $j <= 4; $j++) {
            $val = ($maxVal / 4) * $j;
            $y = round($padT + $innerH - ($j / 4) * $innerH, 2);
            $yLabels[] = ['y' => $y, 'label' => $this->fmtAmt($val)];
        }

        return [
            'vbW' => $vbW,
            'vbH' => $vbH,
            'padL' => $padL,
            'padT' => $padT,
            'padB' => $padB,
            'innerW' => $innerW,
            'innerH' => $innerH,
            'bottomY' => $bottomY,
            'xs' => $xs,
            'yIncome' => $yIncome,
            'yExpense' => $yExpense,
            'incomeArea' => $areaFn($yIncome),
            'expenseArea' => $areaFn($yExpense),
            'incomeLine' => $ptsFn($yIncome),
            'expenseLine' => $ptsFn($yExpense),
            'yLabels' => $yLabels,
            'months' => $months,
        ];
    }

    private function niceMax(float $raw): float
    {
        if ($raw <= 0) {
            return 1000;
        }

        $exp = (int) floor(log10($raw));
        $mag = pow(10, $exp);
        $norm = $raw / $mag;

        $nice = match (true) {
            $norm <= 1.0 => 1.0,
            $norm <= 2.0 => 2.0,
            $norm <= 2.5 => 2.5,
            $norm <= 5.0 => 5.0,
            default => 10.0,
        };

        return $nice * $mag;
    }

    private function fmtAmt(float $val): string
    {
        if ($val >= 1_000_000) {
            return number_format($val / 1_000_000, 1, ',', '.').'M';
        }
        if ($val >= 1_000) {
            return number_format($val / 1_000, 0, ',', '.').'k';
        }

        return number_format($val, 0, ',', '.');
    }
}
