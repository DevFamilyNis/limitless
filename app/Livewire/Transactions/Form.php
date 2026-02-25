<?php

namespace App\Livewire\Transactions;

use App\Domain\Transactions\Actions\UpsertTransactionAction;
use App\Domain\Transactions\DTO\UpsertTransactionData;
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
            ->value('id');
        $this->clientId = (string) Client::query()
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
            ->find((int) $this->invoiceId);

        if (! $invoice) {
            return;
        }

        $this->clientId = (string) $invoice->client_id;

        if ($this->title === '') {
            $this->title = __('messages.transactions.payment_for_invoice').' '.$invoice->invoice_number;
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

        $transaction = app(UpsertTransactionAction::class)->execute(
            UpsertTransactionData::fromArray([
                'user_id' => Auth::id(),
                'transaction_id' => $this->transactionId,
                'category_id' => (int) $validated['categoryId'],
                'client_id' => ! empty($validated['clientId']) ? (int) $validated['clientId'] : null,
                'document_type' => $validated['documentType'],
                'invoice_id' => ! empty($validated['invoiceId']) ? (int) $validated['invoiceId'] : null,
                'date' => $validated['date'],
                'amount' => (float) $validated['amount'],
                'title' => $validated['title'],
                'note' => $validated['note'] ?? null,
            ])
        );

        session()->flash('status', $transaction->wasRecentlyCreated
            ? __('messages.transactions.flash_created')
            : __('messages.transactions.flash_updated'));

        $this->redirectRoute('transactions.index');
    }

    public function render(): View
    {
        $categories = Category::query()
            ->with('type')
            ->orderBy('name')
            ->get();

        $clients = Client::query()
            ->with(['type', 'person'])
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
            'title' => $this->transactionId ? __('messages.transactions.edit_title') : __('messages.transactions.new_title'),
        ]);
    }
}
