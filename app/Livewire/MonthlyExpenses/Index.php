<?php

namespace App\Livewire\MonthlyExpenses;

use App\Domain\MonthlyExpenses\Actions\DeleteMonthlyExpenseItemAction;
use App\Domain\MonthlyExpenses\Actions\UpsertMonthlyExpenseItemAction;
use App\Domain\MonthlyExpenses\DTO\DeleteMonthlyExpenseItemData;
use App\Domain\MonthlyExpenses\DTO\MonthlyExpenseItemsFiltersData;
use App\Domain\MonthlyExpenses\DTO\UpsertMonthlyExpenseItemData;
use App\Domain\MonthlyExpenses\Queries\MonthlyExpenseItemsListQuery;
use App\Models\BillingPeriod;
use App\Models\MonthlyExpenseItem;
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

    public string $title = '';

    public string $amount = '';

    public string $note = '';

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
     * @return array<string, array<int, string>>
     */
    protected function rules(): array
    {
        return [
            'billingPeriodId' => [
                'required',
                Rule::exists('billing_periods', 'id')->where(fn ($query) => $query->whereIn('key', ['monthly', 'yearly'])),
            ],
            'title' => ['required', 'string', 'max:255'],
            'amount' => ['required', 'numeric', 'min:0.01'],
            'note' => ['nullable', 'string'],
        ];
    }

    public function saveItem(): void
    {
        $validated = $this->validate();

        app(UpsertMonthlyExpenseItemAction::class)->execute(
            UpsertMonthlyExpenseItemData::fromArray([
                'user_id' => Auth::id(),
                'item_id' => $this->editingItemId,
                'billing_period_id' => (int) $validated['billingPeriodId'],
                'amount' => (float) $validated['amount'],
                'title' => $validated['title'],
                'note' => $validated['note'] ?? null,
            ])
        );

        session()->flash(
            'status',
            $this->editingItemId === null
                ? __('messages.monthly_expenses.flash_created')
                : __('messages.monthly_expenses.flash_updated')
        );

        $this->resetForm();
    }

    public function editItem(int $itemId): void
    {
        $item = MonthlyExpenseItem::query()
            ->with('billingPeriod')
            ->where('user_id', Auth::id())
            ->whereKey($itemId)
            ->firstOrFail();

        $this->editingItemId = $item->id;
        $this->billingPeriodId = (string) $item->billing_period_id;
        $this->title = $item->title;
        $this->amount = (string) $item->amount;
        $this->note = (string) ($item->note ?? '');
    }

    public function cancelEditing(): void
    {
        $this->resetForm();
    }

    public function deleteItem(int $itemId): void
    {
        app(DeleteMonthlyExpenseItemAction::class)->execute(
            DeleteMonthlyExpenseItemData::fromArray([
                'user_id' => Auth::id(),
                'item_id' => $itemId,
            ])
        );

        if ($this->editingItemId === $itemId) {
            $this->resetForm();
        }

        session()->flash('status', __('messages.monthly_expenses.flash_deleted'));
    }

    public function render(): View
    {
        $filters = MonthlyExpenseItemsFiltersData::fromArray([
            'user_id' => Auth::id(),
            'search' => $this->search,
        ]);

        $result = app(MonthlyExpenseItemsListQuery::class)->execute($filters);

        $billingPeriods = BillingPeriod::query()
            ->whereIn('key', ['monthly', 'yearly'])
            ->orderBy('id')
            ->get();

        if ($this->billingPeriodId === '' && $billingPeriods->isNotEmpty()) {
            $this->billingPeriodId = (string) $billingPeriods->first()->id;
        }

        return view('livewire.monthly-expenses.index', [
            'items' => $result['items'],
            'monthlyTotal' => $result['monthlyTotal'],
            'billingPeriods' => $billingPeriods,
        ])->layout('layouts.app', [
            'title' => __('messages.monthly_expenses.title'),
        ]);
    }

    private function resetForm(): void
    {
        $this->editingItemId = null;
        $this->title = '';
        $this->amount = '';
        $this->note = '';
    }
}
