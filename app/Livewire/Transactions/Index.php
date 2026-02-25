<?php

namespace App\Livewire\Transactions;

use App\Models\Transaction;
use Illuminate\Contracts\View\View;
use Livewire\Component;
use Livewire\WithPagination;

class Index extends Component
{
    use WithPagination;

    public string $month = '';

    public string $year = '';

    public string $search = '';

    public string $typeFilter = 'all';

    public function mount(): void
    {
        $this->month = now()->format('m');
        $this->year = now()->format('Y');
    }

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function updatedTypeFilter(): void
    {
        $this->resetPage();
    }

    public function updatedMonth(): void
    {
        $this->resetPage();
    }

    public function updatedYear(): void
    {
        $this->resetPage();
    }

    public function render(): View
    {
        $transactions = Transaction::query()
            ->with(['category.type', 'client', 'invoice'])
            ->whereYear('date', (int) $this->year)
            ->whereMonth('date', (int) $this->month)
            ->when($this->search !== '', function ($query): void {
                $query->where(function ($innerQuery): void {
                    $innerQuery
                        ->where('title', 'like', '%'.$this->search.'%')
                        ->orWhere('note', 'like', '%'.$this->search.'%')
                        ->orWhere('amount', 'like', '%'.$this->search.'%')
                        ->orWhereHas('client', fn ($clientQuery) => $clientQuery->where('display_name', 'like', '%'.$this->search.'%'))
                        ->orWhereHas('invoice', fn ($invoiceQuery) => $invoiceQuery->where('invoice_number', 'like', '%'.$this->search.'%'))
                        ->orWhereHas('category', fn ($categoryQuery) => $categoryQuery->where('name', 'like', '%'.$this->search.'%'));
                });
            })
            ->when($this->typeFilter !== 'all', function ($query): void {
                $query->whereHas('category.type', fn ($typeQuery) => $typeQuery->where('key', $this->typeFilter));
            })
            ->orderByDesc('date')
            ->orderByDesc('id')
            ->paginate(10);

        $months = collect(range(1, 12))->mapWithKeys(fn (int $month) => [
            str_pad((string) $month, 2, '0', STR_PAD_LEFT) => now()->startOfYear()->addMonths($month - 1)->translatedFormat('F'),
        ])->all();

        $years = collect(range(now()->year - 5, now()->year + 1))
            ->reverse()
            ->mapWithKeys(fn (int $year) => [(string) $year => (string) $year])
            ->all();

        return view('livewire.transactions.index', [
            'transactions' => $transactions,
            'months' => $months,
            'years' => $years,
        ])->layout('layouts.app', [
            'title' => __('messages.transactions.title'),
        ]);
    }
}
