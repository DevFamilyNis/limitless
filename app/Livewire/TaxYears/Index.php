<?php

namespace App\Livewire\TaxYears;

use App\Models\TaxYear;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Livewire\WithPagination;

class Index extends Component
{
    use WithPagination;

    public string $search = '';

    public function mount(): void
    {
        TaxYear::query()->firstOrCreate(
            [
                'user_id' => Auth::id(),
                'year' => now()->year,
            ],
            [
                'first_threshold_amount' => 6000000,
                'second_threshold_amount' => 8000000,
            ]
        );
    }

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function deleteTaxYear(int $taxYearId): void
    {
        $taxYear = TaxYear::query()
            ->where('user_id', Auth::id())
            ->findOrFail($taxYearId);

        $taxYear->delete();

        session()->flash('status', 'Poreska godina je uspešno obrisana.');
    }

    public function render(): View
    {
        $taxYears = TaxYear::query()
            ->where('user_id', Auth::id())
            ->when($this->search !== '', fn ($query) => $query->where('year', 'like', '%'.$this->search.'%'))
            ->orderByDesc('year')
            ->paginate(10);

        return view('livewire.tax-years.index', [
            'taxYears' => $taxYears,
        ])->layout('layouts.app', [
            'title' => 'Poreske godine',
        ]);
    }
}
