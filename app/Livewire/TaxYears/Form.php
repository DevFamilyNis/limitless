<?php

namespace App\Livewire\TaxYears;

use App\Domain\TaxYears\Actions\UpsertTaxYearAction;
use App\Domain\TaxYears\DTO\UpsertTaxYearData;
use App\Models\TaxYear;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Livewire\Component;

class Form extends Component
{
    public ?int $taxYearId = null;

    public string $year = '';

    public string $firstThresholdAmount = '';

    public string $secondThresholdAmount = '';

    public function mount(?TaxYear $taxYear = null): void
    {

        if ($taxYear?->exists) {
            $this->taxYearId = $taxYear->id;
            $this->year = (string) $taxYear->year;
            $this->firstThresholdAmount = (string) $taxYear->first_threshold_amount;
            $this->secondThresholdAmount = (string) $taxYear->second_threshold_amount;

            return;
        }

        $this->year = (string) now()->year;
        $this->firstThresholdAmount = '6000000.00';
        $this->secondThresholdAmount = '8000000.00';
    }

    /**
     * @return array<string, mixed>
     */
    protected function rules(): array
    {
        return [
            'year' => [
                'required',
                'integer',
                'digits:4',
                Rule::unique('tax_years', 'year')->ignore($this->taxYearId),
            ],
            'firstThresholdAmount' => ['required', 'numeric', 'min:0.01'],
            'secondThresholdAmount' => ['required', 'numeric', 'gt:firstThresholdAmount'],
        ];
    }

    public function save(): void
    {
        $validated = $this->validate();

        $taxYear = app(UpsertTaxYearAction::class)->execute(
            UpsertTaxYearData::fromArray([
                'user_id' => Auth::id(),
                'tax_year_id' => $this->taxYearId,
                'year' => (int) $validated['year'],
                'first_threshold_amount' => (float) $validated['firstThresholdAmount'],
                'second_threshold_amount' => (float) $validated['secondThresholdAmount'],
            ])
        );

        session()->flash('status', $taxYear->wasRecentlyCreated
            ? __('messages.tax_years.flash_created')
            : __('messages.tax_years.flash_updated'));

        $this->redirectRoute('tax-years.index');
    }

    public function render(): View
    {
        return view('livewire.tax-years.form', [
            'isEditing' => $this->taxYearId !== null,
        ])->layout('layouts.app', [
            'title' => $this->taxYearId ? __('messages.tax_years.edit_title') : __('messages.tax_years.new_title'),
        ]);
    }
}
