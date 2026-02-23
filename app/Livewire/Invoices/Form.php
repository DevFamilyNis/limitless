<?php

namespace App\Livewire\Invoices;

use App\Domain\Invoices\Actions\UpsertInvoiceAction;
use App\Domain\Invoices\DTO\UpsertInvoiceData;
use App\Models\Client;
use App\Models\ClientProjectRate;
use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\InvoiceStatus;
use App\Models\Project;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class Form extends Component
{
    public ?int $invoiceId = null;

    public string $clientId = '';

    public string $statusId = '';

    public string $invoiceYear = '';

    public string $invoiceSeq = '';

    public string $invoiceNumber = '';

    public string $issueDate = '';

    public string $dueDate = '';

    public string $total = '0.00';

    public string $note = '';

    /**
     * @var array<int, array{
     *     id:int|null,
     *     projectId:string,
     *     clientProjectRateId:string,
     *     description:string,
     *     quantity:string,
     *     unitPrice:string,
     *     amount:string
     * }>
     */
    public array $items = [];

    public function mount(UpsertInvoiceAction $upsertInvoiceAction, ?Invoice $invoice = null): void
    {
        if ($invoice?->exists && $invoice->client->user_id !== Auth::id()) {
            abort(404);
        }

        if ($invoice?->exists) {
            $invoice->load(['items']);

            $this->invoiceId = $invoice->id;
            $this->clientId = (string) $invoice->client_id;
            $this->statusId = (string) $invoice->status_id;
            $this->invoiceYear = (string) $invoice->invoice_year;
            $this->invoiceSeq = (string) $invoice->invoice_seq;
            $this->invoiceNumber = $invoice->invoice_number;
            $this->issueDate = $invoice->issue_date?->format('Y-m-d') ?? '';
            $this->dueDate = $invoice->due_date?->format('Y-m-d') ?? '';
            $this->total = (string) $invoice->total;
            $this->note = (string) $invoice->note;
            $this->items = $invoice->items
                ->map(fn (InvoiceItem $item): array => [
                    'id' => $item->id,
                    'projectId' => (string) $item->project_id,
                    'clientProjectRateId' => (string) ($item->client_project_rate_id ?? ''),
                    'description' => $item->description,
                    'quantity' => (string) $item->quantity,
                    'unitPrice' => (string) $item->unit_price,
                    'amount' => (string) $item->amount,
                ])
                ->values()
                ->all();

            return;
        }

        $this->clientId = (string) Client::query()
            ->where('user_id', Auth::id())
            ->where('is_active', true)
            ->value('id');

        $this->statusId = (string) InvoiceStatus::query()
            ->where('key', 'draft')
            ->value('id');

        $this->issueDate = now()->startOfMonth()->subDay()->format('Y-m-d');
        $this->dueDate = now()->startOfMonth()->addDays(14)->format('Y-m-d');

        $preview = $upsertInvoiceAction->preview();
        $this->invoiceYear = (string) $preview['invoice_year'];
        $this->invoiceSeq = (string) $preview['invoice_seq'];
        $this->invoiceNumber = $preview['invoice_number'];

        $this->updatedClientId();
    }

    /**
     * @return array<string, array<int, string>>
     */
    protected function rules(): array
    {
        return [
            'clientId' => ['required', 'exists:clients,id'],
            'statusId' => ['required', 'exists:invoice_statuses,id'],
            'issueDate' => ['required', 'date'],
            'dueDate' => ['nullable', 'date', 'after_or_equal:issueDate'],
            'total' => ['required', 'numeric', 'min:0'],
            'note' => ['nullable', 'string'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.projectId' => ['required', 'exists:projects,id'],
            'items.*.clientProjectRateId' => ['nullable'],
            'items.*.description' => ['required', 'string', 'max:255'],
            'items.*.quantity' => ['required', 'numeric', 'min:0.01'],
            'items.*.unitPrice' => ['required', 'numeric', 'min:0'],
            'items.*.amount' => ['required', 'numeric', 'min:0'],
        ];
    }

    public function updatedClientId(): void
    {
        $rates = $this->selectedClientRates();

        if ($rates->isNotEmpty()) {
            $this->items = $rates
                ->map(fn (ClientProjectRate $rate): array => [
                    'id' => null,
                    'projectId' => (string) $rate->project_id,
                    'clientProjectRateId' => (string) $rate->id,
                    'description' => $rate->project?->name ?? 'Usluga',
                    'quantity' => '1.00',
                    'unitPrice' => (string) $rate->price_amount,
                    'amount' => (string) $rate->price_amount,
                ])
                ->values()
                ->all();
        } else {
            $this->items = [];
        }

        $this->recalculateAllItems();
    }

    public function updatedItems(mixed $value, ?string $name = null): void
    {
        if ($name === null) {
            $this->recalculateAllItems();

            return;
        }

        $segments = explode('.', $name);

        if (count($segments) < 3) {
            return;
        }

        $itemIndex = (int) $segments[1];
        $field = $segments[2];

        if (! isset($this->items[$itemIndex])) {
            return;
        }

        if ($field === 'projectId') {
            $this->hydrateItemFromSelection($itemIndex);
        } else {
            $this->recalculateItemAmount($itemIndex);
        }
    }

    public function addItem(): void
    {
        if ($this->selectedClientRates()->isEmpty()) {
            return;
        }

        $this->items[] = [
            'id' => null,
            'projectId' => '',
            'clientProjectRateId' => '',
            'description' => '',
            'quantity' => '1.00',
            'unitPrice' => '0.00',
            'amount' => '0.00',
        ];
    }

    public function removeItem(int $index): void
    {
        unset($this->items[$index]);
        $this->items = array_values($this->items);

        if ($this->items === [] && $this->selectedClientRates()->isNotEmpty()) {
            $this->addItem();
        }

        $this->recalculateAllItems();
    }

    public function save(): void
    {
        $this->recalculateAllItems();

        $validated = $this->validate();

        $invoice = app(UpsertInvoiceAction::class)->execute(
            UpsertInvoiceData::fromArray([
                'user_id' => Auth::id(),
                'invoice_id' => $this->invoiceId,
                'client_id' => (int) $validated['clientId'],
                'status_id' => (int) $validated['statusId'],
                'issue_date' => $validated['issueDate'],
                'due_date' => $validated['dueDate'] ?: null,
                'total' => (float) $validated['total'],
                'note' => $validated['note'] ?? null,
                'items' => $validated['items'],
            ])
        );

        if ($this->invoiceId === null) {
            $this->invoiceYear = (string) $invoice->invoice_year;
            $this->invoiceSeq = (string) $invoice->invoice_seq;
            $this->invoiceNumber = $invoice->invoice_number;
        }

        session()->flash('status', $invoice->wasRecentlyCreated
            ? 'Faktura je uspešno dodata.'
            : 'Faktura je uspešno izmenjena.');

        $this->redirectRoute('invoices.index');
    }

    public function render(): View
    {
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

        $serviceRates = $this->selectedClientRates();
        $hasClientPriceList = $serviceRates->isNotEmpty();
        $hasRequiredData = $clients->isNotEmpty() && ($this->invoiceId !== null || $hasClientPriceList);

        return view('livewire.invoices.form', [
            'isEditing' => $this->invoiceId !== null,
            'clients' => $clients,
            'hasClientPriceList' => $hasClientPriceList,
            'statuses' => InvoiceStatus::query()->orderBy('id')->get(),
            'hasRequiredData' => $hasRequiredData,
        ])->layout('layouts.app', [
            'title' => $this->invoiceId ? 'Izmena fakture' : 'Nova faktura',
        ]);
    }

    protected function hydrateItemFromSelection(int $index): void
    {
        $projectId = (int) ($this->items[$index]['projectId'] ?? 0);

        if ($projectId === 0) {
            $this->items[$index]['clientProjectRateId'] = '';
            $this->items[$index]['description'] = '';
            $this->items[$index]['unitPrice'] = '0.00';
            $this->recalculateItemAmount($index);

            return;
        }

        $project = Project::query()
            ->where('user_id', Auth::id())
            ->find($projectId);

        if ($project) {
            $this->items[$index]['description'] = $project->name;
        }

        $rate = null;

        if ($this->clientId !== '') {
            $rate = ClientProjectRate::query()
                ->where('client_id', (int) $this->clientId)
                ->where('project_id', $projectId)
                ->where('is_active', true)
                ->latest('id')
                ->first();
        }

        $this->items[$index]['clientProjectRateId'] = $rate ? (string) $rate->id : '';

        if ($rate) {
            $this->items[$index]['unitPrice'] = (string) $rate->price_amount;
        }

        $this->recalculateItemAmount($index);
    }

    protected function recalculateAllItems(): void
    {
        foreach (array_keys($this->items) as $index) {
            $this->recalculateItemAmount((int) $index, false);
        }

        $this->recalculateTotals();
    }

    protected function recalculateItemAmount(int $index, bool $recalculateTotals = true): void
    {
        $quantity = (float) ($this->items[$index]['quantity'] ?? 0);
        $unitPrice = (float) ($this->items[$index]['unitPrice'] ?? 0);
        $amount = max(0, round($quantity * $unitPrice, 2));

        $this->items[$index]['amount'] = number_format($amount, 2, '.', '');

        if ($recalculateTotals) {
            $this->recalculateTotals();
        }
    }

    protected function recalculateTotals(): void
    {
        $sum = collect($this->items)->sum(fn (array $item): float => (float) ($item['amount'] ?? 0));
        $formatted = number_format($sum, 2, '.', '');

        $this->total = $formatted;
    }

    /**
     * @return Collection<int, ClientProjectRate>
     */
    protected function selectedClientRates(): Collection
    {
        if ($this->clientId === '') {
            return collect();
        }

        return ClientProjectRate::query()
            ->with(['project'])
            ->where('client_id', (int) $this->clientId)
            ->where('is_active', true)
            ->orderBy('id')
            ->get();
    }
}
