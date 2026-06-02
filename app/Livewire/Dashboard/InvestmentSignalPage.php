<?php

declare(strict_types=1);

namespace App\Livewire\Dashboard;

use App\Domain\Dashboard\Queries\YearlyCashflowQuery;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\Url;
use Livewire\Component;

class InvestmentSignalPage extends Component
{
    #[Url]
    public int $year = 0;

    public function mount(): void
    {
        $available = (new YearlyCashflowQuery)->availableYears();

        if ($this->year === 0 || ! in_array($this->year, $available, true)) {
            $this->year = $available[0] ?? now()->year;
        }
    }

    public function render(): View
    {
        $query = new YearlyCashflowQuery;
        $data = $query->execute($this->year);
        $years = $query->availableYears();

        return view('livewire.dashboard.investment-signal-page', [
            'data' => $data,
            'years' => $years,
        ])->layout('layouts.app', [
            'title' => 'Detalji investicionog signala',
        ]);
    }
}
