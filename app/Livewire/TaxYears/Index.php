<?php

namespace App\Livewire\TaxYears;

use App\Domain\TaxYears\Actions\DeleteTaxYearAction;
use App\Domain\TaxYears\Actions\EnsureCurrentTaxYearAction;
use App\Domain\TaxYears\DTO\DeleteTaxYearData;
use App\Domain\TaxYears\DTO\EnsureCurrentTaxYearData;
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
        app(EnsureCurrentTaxYearAction::class)->execute(
            EnsureCurrentTaxYearData::fromArray([
                'user_id' => Auth::id(),
            ])
        );
    }

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function deleteTaxYear(int $taxYearId): void
    {
        app(DeleteTaxYearAction::class)->execute(
            DeleteTaxYearData::fromArray([
                'user_id' => Auth::id(),
                'tax_year_id' => $taxYearId,
            ])
        );

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
