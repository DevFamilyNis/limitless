<?php

namespace App\Livewire\MonthlyExpenses;

use App\Domain\Transactions\Actions\DeleteTransactionAction;
use App\Domain\Transactions\Actions\UpsertTransactionAction;
use App\Domain\Transactions\DTO\DeleteTransactionData;
use App\Domain\Transactions\DTO\MonthlyExpensesFiltersData;
use App\Domain\Transactions\DTO\UpsertTransactionData;
use App\Domain\Transactions\Queries\MonthlyExpensesListQuery;
use App\Models\Category;
use App\Models\Transaction;
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

    public ?int $editingExpenseId = null;

    public string $categoryId = '';

    public string $date = '';

    public string $title = '';

    public string $amount = '';

    public string $note = '';

    public function mount(): void
    {
        $this->month = now()->format('m');
        $this->year = now()->format('Y');
        $this->date = now()->toDateString();
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
            'title' => ['required', 'string', 'max:255'],
            'amount' => ['required', 'numeric', 'min:0.01'],
            'note' => ['nullable', 'string'],
        ];
    }

    public function saveExpense(): void
    {
        $validated = $this->validate();

        app(UpsertTransactionAction::class)->execute(
            UpsertTransactionData::fromArray([
                'user_id' => Auth::id(),
                'transaction_id' => $this->editingExpenseId,
                'category_id' => (int) $validated['categoryId'],
                'client_id' => null,
                'document_type' => 'fiscal',
                'invoice_id' => null,
                'date' => $validated['date'],
                'amount' => (float) $validated['amount'],
                'title' => $validated['title'],
                'note' => $validated['note'] ?? null,
            ])
        );

        session()->flash(
            'status',
            $this->editingExpenseId === null
                ? __('messages.monthly_expenses.flash_created')
                : __('messages.monthly_expenses.flash_updated')
        );

        $this->resetForm();
    }

    public function editExpense(int $transactionId): void
    {
        $expense = Transaction::query()
            ->with('category.type')
            ->where('user_id', Auth::id())
            ->whereKey($transactionId)
            ->whereHas('category.type', fn ($typeQuery) => $typeQuery->where('key', 'expense'))
            ->firstOrFail();

        $this->editingExpenseId = $expense->id;
        $this->categoryId = (string) $expense->category_id;
        $this->date = $expense->date?->toDateString() ?? now()->toDateString();
        $this->title = $expense->title;
        $this->amount = (string) $expense->amount;
        $this->note = (string) ($expense->note ?? '');
    }

    public function cancelEditing(): void
    {
        $this->resetForm();
    }

    public function deleteExpense(int $transactionId): void
    {
        app(DeleteTransactionAction::class)->execute(
            DeleteTransactionData::fromArray([
                'user_id' => Auth::id(),
                'transaction_id' => $transactionId,
            ])
        );

        if ($this->editingExpenseId === $transactionId) {
            $this->resetForm();
        }

        session()->flash('status', __('messages.monthly_expenses.flash_deleted'));
    }

    public function render(): View
    {
        $filters = MonthlyExpensesFiltersData::fromArray([
            'user_id' => Auth::id(),
            'month' => (int) $this->month,
            'year' => (int) $this->year,
            'search' => $this->search,
        ]);

        $result = app(MonthlyExpensesListQuery::class)->execute($filters);

        $expenseCategories = Category::query()
            ->where('user_id', Auth::id())
            ->whereHas('type', fn ($typeQuery) => $typeQuery->where('key', 'expense'))
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

        return view('livewire.monthly-expenses.index', [
            'expenses' => $result['expenses'],
            'totalAmount' => $result['totalAmount'],
            'expenseCategories' => $expenseCategories,
            'months' => $months,
            'years' => $years,
        ])->layout('layouts.app', [
            'title' => __('messages.monthly_expenses.title'),
        ]);
    }

    private function resetForm(): void
    {
        $this->editingExpenseId = null;
        $this->title = '';
        $this->amount = '';
        $this->note = '';
        $this->date = now()->setDate((int) $this->year, (int) $this->month, min(now()->day, 28))->toDateString();
    }
}
