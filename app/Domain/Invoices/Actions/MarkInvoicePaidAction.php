<?php

declare(strict_types=1);

namespace App\Domain\Invoices\Actions;

use App\Domain\Invoices\DTO\MarkInvoicePaidData;
use App\Models\Invoice;
use App\Models\InvoiceStatus;

final class MarkInvoicePaidAction
{
    public function execute(MarkInvoicePaidData $dto): Invoice
    {
        $paidStatusId = InvoiceStatus::query()->where('key', 'paid')->value('id');

        if ($paidStatusId === null) {
            abort(422, 'Paid status missing.');
        }

        $invoice = Invoice::query()
            ->findOrFail($dto->invoiceId);

        $invoice->update(['status_id' => $paidStatusId]);

        return $invoice;
    }
}
