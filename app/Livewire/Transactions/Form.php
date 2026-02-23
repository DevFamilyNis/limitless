<?php

namespace App\Livewire\Transactions;

use App\Models\Category;
use App\Models\Client;
use App\Models\Invoice;
use App\Models\Transaction;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class Form extends Component
{
    public ?int $transactionId = null;

    public string $categoryId = '';

    public string $clientId = '';

    public string $documentType = 'invoice';

    public string $invoiceId = '';

    public string $date = '';

    public string $amount = '';

    public string $title = '';

    public string $note = '';

    public function mount(?Transaction $transaction = null): void
    {
        if ($transaction?->exists && $transaction->user_id !== Auth::id()) {
            abort(404);
        }

        if ($transaction?->exists) {
            $this->transactionId = $transaction->id;
            $this->categoryId = (string) $transaction->category_id;
            $this->clientId = (string) ($transaction->client_id ?? '');
            $this->invoiceId = (string) ($transaction->invoice_id ?? '');
            $this->documentType = $transaction->invoice_id ? 'invoice' : 'fiscal';
            $this->date = $transaction->date?->format('Y-m-d') ?? '';
            $this->amount = (string) $transaction->amount;
            $this->title = $transaction->title;
            $this->note = (string) $transaction->note;

            return;
        }

        $this->categoryId = (string) Category::query()
            ->where('user_id', Auth::id())
            ->value('id');
        $this->clientId = (string) Client::query()
            ->where('user_id', Auth::id())
            ->where('is_active', true)
            ->value('id');
        $this->date = now()->toDateString();
    }

    public function updatedDocumentType(): void
    {
        if ($this->documentType === 'fiscal') {
            $this->invoiceId = '';
        }
    }

    public function updatedInvoiceId(): void
    {
        if ($this->invoiceId === '') {
            return;
        }

        $invoice = Invoice::query()
            ->whereHas('client', fn ($query) => $query->where('user_id', Auth::id()))
            ->find((int) $this->invoiceId);

        if (! $invoice) {
            return;
        }

        $this->clientId = (string) $invoice->client_id;

        if ($this->title === '') {
            $this->title = 'Uplata po fakturi '.$invoice->invoice_number;
        }
    }

    /**
     * @return array<string, array<int, string>>
     */
    protected function rules(): array
    {
        return [
            'categoryId' => ['required', 'exists:categories,id'],
            'clientId' => ['nullable', 'exists:clients,id'],
            'documentType' => ['required', 'in:invoice,fiscal'],
            'invoiceId' => ['nullable', 'required_if:documentType,invoice', 'exists:invoices,id'],
            'date' => ['required', 'date'],
            'amount' => ['required', 'numeric', 'min:0.01'],
            'title' => ['required', 'string', 'max:255'],
            'note' => ['nullable', 'string'],
        ];
    }

    public function save(): void
    {
        $validated = $this->validate();

        $category = Category::query()
            ->where('user_id', Auth::id())
            ->findOrFail((int) $validated['categoryId']);

        $clientId = null;
        if (! empty($validated['clientId'])) {
            $clientId = Client::query()
                ->where('user_id', Auth::id())
                ->findOrFail((int) $validated['clientId'])
                ->id;
        }

        $invoiceId = null;
        if ($validated['documentType'] === 'invoice') {
            $invoiceId = Invoice::query()
                ->whereHas('client', fn ($query) => $query->where('user_id', Auth::id()))
                ->findOrFail((int) $validated['invoiceId'])
                ->id;
        }

        $transaction = $this->transactionId
            ? Transaction::query()
                ->where('user_id', Auth::id())
                ->findOrFail($this->transactionId)
            : new Transaction;

        $transaction->fill([
            'user_id' => Auth::id(),
            'category_id' => $category->id,
            'client_id' => $clientId,
            'invoice_id' => $invoiceId,
            'date' => $validated['date'],
            'amount' => $validated['amount'],
            'currency' => 'RSD',
            'title' => trim($validated['title']),
            'note' => $validated['note'] ?: null,
        ]);

        $transaction->save();

        session()->flash('status', $transaction->wasRecentlyCreated
            ? 'Transakcija je uspešno dodata.'
            : 'Transakcija je uspešno izmenjena.');

        $this->redirectRoute('transactions.index');
    }

    public function render(): View
    {
        $categories = Category::query()
            ->with('type')
            ->where('user_id', Auth::id())
            ->orderBy('name')
            ->get();

        $clients = Client::query()
            ->with(['type', 'person'])
            ->where('user_id', Auth::id())
            ->where(function ($query): void {
                $query->where('is_active', true);

                if ($this->clientId !== '') {
                    $query->orWhere('id', (int) $this->clientId);
                }
            })
            ->orderBy('display_name')
            ->get();

        $invoices = Invoice::query()
            ->with('client')
            ->whereHas('client', fn ($query) => $query->where('user_id', Auth::id()))
            ->when($this->clientId !== '', fn ($query) => $query->where('client_id', (int) $this->clientId))
            ->orderByDesc('issue_date')
            ->limit(50)
            ->get();

        return view('livewire.transactions.form', [
            'isEditing' => $this->transactionId !== null,
            'categories' => $categories,
            'clients' => $clients,
            'invoices' => $invoices,
            'hasRequiredData' => $categories->isNotEmpty(),
        ])->layout('layouts.app', [
            'title' => $this->transactionId ? 'Izmena transakcije' : 'Nova transakcija',
        ]);
    }
}
