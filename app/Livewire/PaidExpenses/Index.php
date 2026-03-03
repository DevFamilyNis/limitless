<?php

namespace App\Livewire\PaidExpenses;

use App\Domain\PaidExpenses\Actions\DeletePaidExpenseAction;
use App\Domain\PaidExpenses\Actions\UpsertPaidExpenseAction;
use App\Domain\PaidExpenses\DTO\DeletePaidExpenseData;
use App\Domain\PaidExpenses\DTO\PaidExpensesFiltersData;
use App\Domain\PaidExpenses\DTO\UpsertPaidExpenseData;
use App\Domain\PaidExpenses\Queries\PaidExpensesListQuery;
use App\Models\Category;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Livewire\WithPagination;

class Index extends Component
{
    use WithPagination;

    public string $month = '';

    public string $year = '';

    public string $search = '';

    public ?int $editingTransactionId = null;

    public string $categoryId = '';

    public string $date = '';

    public string $amount = '';

    public string $title = '';

    public string $note = '';

    public function mount(): void
    {
        $this->month = now()->format('m');
        $this->year = now()->format('Y');
        $this->date = now()->toDateString();

        $this->categoryId = (string) Category::query()
            ->join('category_types', 'category_types.id', '=', 'categories.category_type_id')
            ->where('category_types.key', 'expense')
            ->value('categories.id');
    }

    public function updatedMonth(): void
    {
        $this->resetPage();
    }

    public function updatedYear(): void
    {
        $this->resetPage();
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
            'categoryId' => ['required', 'exists:categories,id'],
            'date' => ['required', 'date'],
            'amount' => ['required', 'numeric', 'min:0.01'],
            'title' => ['required', 'string', 'max:255'],
            'note' => ['nullable', 'string'],
        ];
    }

    public function saveExpense(): void
    {
        $validated = $this->validate();

        app(UpsertPaidExpenseAction::class)->execute(
            UpsertPaidExpenseData::fromArray([
                'user_id' => Auth::id(),
                'transaction_id' => $this->editingTransactionId,
                'category_id' => (int) $validated['categoryId'],
                'date' => $validated['date'],
                'amount' => (float) $validated['amount'],
                'title' => $validated['title'],
                'note' => $validated['note'] ?? null,
            ])
        );

        session()->flash(
            'status',
            $this->editingTransactionId === null
                ? __('messages.paid_expenses.flash_created')
                : __('messages.paid_expenses.flash_updated')
        );

        $this->resetForm();
    }

    public function editExpense(int $transactionId): void
    {
        $transaction = app(PaidExpensesListQuery::class)->findExpenseTransaction($transactionId);

        $this->editingTransactionId = $transaction->id;
        $this->categoryId = (string) $transaction->category_id;
        $this->date = $transaction->date?->toDateString() ?? now()->toDateString();
        $this->amount = (string) $transaction->amount;
        $this->title = $transaction->title;
        $this->note = (string) ($transaction->note ?? '');
    }

    public function cancelEditing(): void
    {
        $this->resetForm();
    }

    public function deleteExpense(int $transactionId): void
    {
        app(DeletePaidExpenseAction::class)->execute(
            DeletePaidExpenseData::fromArray([
                'transaction_id' => $transactionId,
            ])
        );

        if ($this->editingTransactionId === $transactionId) {
            $this->resetForm();
        }

        session()->flash('status', __('messages.paid_expenses.flash_deleted'));
    }

    public function render(): View
    {
        $filters = PaidExpensesFiltersData::fromArray([
            'month' => $this->month,
            'year' => $this->year,
            'search' => $this->search,
        ]);

        $result = app(PaidExpensesListQuery::class)->execute($filters);

        $expenseCategories = Category::query()
            ->with('type')
            ->whereHas('type', fn ($query) => $query->where('key', 'expense'))
            ->orderBy('name')
            ->get();

        if ($this->categoryId === '' && $expenseCategories->isNotEmpty()) {
            $this->categoryId = (string) $expenseCategories->first()->id;
        }

        $months = collect(range(1, 12))->mapWithKeys(fn (int $month) => [
            str_pad((string) $month, 2, '0', STR_PAD_LEFT) => now()->startOfYear()->addMonths($month - 1)->translatedFormat('F'),
        ])->all();

        $years = collect(range(now()->year - 5, now()->year + 1))
            ->reverse()
            ->mapWithKeys(fn (int $year) => [(string) $year => (string) $year])
            ->all();

        return view('livewire.paid-expenses.index', [
            'transactions' => $result['transactions'],
            'monthlyTotal' => $result['monthlyTotal'],
            'expenseCategories' => $expenseCategories,
            'months' => $months,
            'years' => $years,
        ])->layout('layouts.app', [
            'title' => __('messages.paid_expenses.title'),
        ]);
    }

    private function resetForm(): void
    {
        $this->editingTransactionId = null;
        $this->date = now()->toDateString();
        $this->amount = '';
        $this->title = '';
        $this->note = '';
    }
}
