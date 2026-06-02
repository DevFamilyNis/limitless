<?php

namespace App\Livewire\MonthlyIncomes;

use App\Domain\MonthlyIncomes\Actions\DeleteMonthlyIncomeItemAction;
use App\Domain\MonthlyIncomes\Actions\UpsertMonthlyIncomeItemAction;
use App\Domain\MonthlyIncomes\DTO\DeleteMonthlyIncomeItemData;
use App\Domain\MonthlyIncomes\DTO\MonthlyIncomeItemsFiltersData;
use App\Domain\MonthlyIncomes\DTO\UpsertMonthlyIncomeItemData;
use App\Domain\MonthlyIncomes\Queries\MonthlyIncomeItemsListQuery;
use App\Models\BillingPeriod;
use App\Models\MonthlyIncomeItem;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Livewire\Component;
use Livewire\WithPagination;

class Index extends Component
{
    use WithPagination;

    public string $search = '';

    public ?int $editingItemId = null;

    public string $billingPeriodId = '';

    public string $name = '';

    public string $price = '';

    public string $description = '';

    public function mount(): void
    {
        $this->billingPeriodId = (string) BillingPeriod::query()
            ->where('key', 'monthly')
            ->value('id');
    }

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    /**
     * @return array<string, array<int, string|\Illuminate\Validation\Rules\Exists>>
     */
    protected function rules(): array
    {
        return [
            'billingPeriodId' => [
                'required',
                Rule::exists('billing_periods', 'id')->where(fn ($query) => $query->whereIn('key', ['monthly', 'yearly'])),
            ],
            'name' => ['required', 'string', 'max:255'],
            'price' => ['required', 'numeric', 'min:0.01'],
            'description' => ['nullable', 'string'],
        ];
    }

    public function saveItem(): void
    {
        $validated = $this->validate();

        app(UpsertMonthlyIncomeItemAction::class)->execute(
            UpsertMonthlyIncomeItemData::fromArray([
                'user_id' => Auth::id(),
                'item_id' => $this->editingItemId,
                'billing_period_id' => (int) $validated['billingPeriodId'],
                'name' => $validated['name'],
                'price' => (float) $validated['price'],
                'description' => $validated['description'] ?? null,
            ])
        );

        session()->flash(
            'status',
            $this->editingItemId === null
                ? __('messages.monthly_incomes.flash_created')
                : __('messages.monthly_incomes.flash_updated')
        );

        $this->resetForm();
    }

    public function editItem(int $itemId): void
    {
        $item = MonthlyIncomeItem::query()
            ->with('billingPeriod')
            ->where('user_id', Auth::id())
            ->whereKey($itemId)
            ->firstOrFail();

        $this->editingItemId = $item->id;
        $this->billingPeriodId = (string) $item->billing_period_id;
        $this->name = $item->name;
        $this->price = (string) $item->price;
        $this->description = (string) ($item->description ?? '');
    }

    public function cancelEditing(): void
    {
        $this->resetForm();
    }

    public function deleteItem(int $itemId): void
    {
        app(DeleteMonthlyIncomeItemAction::class)->execute(
            DeleteMonthlyIncomeItemData::fromArray([
                'user_id' => Auth::id(),
                'item_id' => $itemId,
            ])
        );

        if ($this->editingItemId === $itemId) {
            $this->resetForm();
        }

        session()->flash('status', __('messages.monthly_incomes.flash_deleted'));
    }

    public function render(): View
    {
        $filters = MonthlyIncomeItemsFiltersData::fromArray([
            'user_id' => Auth::id(),
            'search' => $this->search,
        ]);

        $result = app(MonthlyIncomeItemsListQuery::class)->execute($filters);

        $billingPeriods = BillingPeriod::query()
            ->whereIn('key', ['monthly', 'yearly'])
            ->orderBy('id')
            ->get();

        if ($this->billingPeriodId === '' && $billingPeriods->isNotEmpty()) {
            $this->billingPeriodId = (string) $billingPeriods->first()->id;
        }

        return view('livewire.monthly-incomes.index', [
            'items' => $result['items'],
            'monthlyTotal' => $result['monthlyTotal'],
            'billingPeriods' => $billingPeriods,
        ])->layout('layouts.app', [
            'title' => __('messages.monthly_incomes.title'),
        ]);
    }

    private function resetForm(): void
    {
        $this->editingItemId = null;
        $this->name = '';
        $this->price = '';
        $this->description = '';
    }
}
