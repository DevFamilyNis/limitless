<?php

namespace App\Livewire\Invoices;

use App\Domain\Invoices\Actions\DeleteInvoiceAction;
use App\Domain\Invoices\Actions\GenerateInvoicePdfAction;
use App\Domain\Invoices\Actions\MarkInvoicePaidAction;
use App\Domain\Invoices\DTO\DeleteInvoiceData;
use App\Domain\Invoices\DTO\GenerateInvoicePdfData;
use App\Domain\Invoices\DTO\MarkInvoicePaidData;
use App\Domain\Invoices\Exceptions\InvoicePdfGenerationException;
use App\Models\Invoice;
use App\Models\InvoiceStatus;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Livewire\WithPagination;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class Index extends Component
{
    use WithPagination;

    public string $search = '';

    public string $statusFilter = 'all';

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function updatedStatusFilter(): void
    {
        $this->resetPage();
    }

    public function markAsPaid(int $invoiceId): void
    {
        app(MarkInvoicePaidAction::class)->execute(
            MarkInvoicePaidData::fromArray([
                'user_id' => Auth::id(),
                'invoice_id' => $invoiceId,
            ])
        );

        session()->flash('status', 'Faktura je označena kao plaćena.');
    }

    public function deleteInvoice(int $invoiceId): void
    {
        app(DeleteInvoiceAction::class)->execute(
            DeleteInvoiceData::fromArray([
                'user_id' => Auth::id(),
                'invoice_id' => $invoiceId,
            ])
        );

        session()->flash('status', 'Faktura je uspešno obrisana.');
    }

    public function downloadPdf(int $invoiceId): ?BinaryFileResponse
    {
        try {
            $result = app(GenerateInvoicePdfAction::class)->execute(
                GenerateInvoicePdfData::fromArray([
                    'user_id' => Auth::id(),
                    'invoice_id' => $invoiceId,
                ])
            );
        } catch (InvoicePdfGenerationException $exception) {
            session()->flash('status', $exception->getMessage());

            return null;
        }

        return response()
            ->download($result['path'], $result['filename'])
            ->deleteFileAfterSend(true);
    }

    public function render(): View
    {
        $invoices = Invoice::query()
            ->with(['client.type', 'client.person', 'status'])
            ->withCount('items')
            ->whereHas('client', fn ($query) => $query->where('user_id', Auth::id()))
            ->when($this->search !== '', function ($query): void {
                $query->where(function ($innerQuery): void {
                    $innerQuery
                        ->where('invoice_number', 'like', '%'.$this->search.'%')
                        ->orWhere('note', 'like', '%'.$this->search.'%')
                        ->orWhereHas('client', function ($clientQuery): void {
                            $clientQuery
                                ->where('display_name', 'like', '%'.$this->search.'%')
                                ->orWhereHas('person', function ($personQuery): void {
                                    $personQuery
                                        ->where('first_name', 'like', '%'.$this->search.'%')
                                        ->orWhere('last_name', 'like', '%'.$this->search.'%');
                                });
                        });
                });
            })
            ->when($this->statusFilter !== 'all', fn ($query) => $query->where('status_id', (int) $this->statusFilter))
            ->orderByDesc('issue_date')
            ->orderByDesc('id')
            ->paginate(10);

        return view('livewire.invoices.index', [
            'invoices' => $invoices,
            'statuses' => InvoiceStatus::query()->orderBy('id')->get(),
        ])->layout('layouts.app', [
            'title' => 'Fakture',
        ]);
    }
}
