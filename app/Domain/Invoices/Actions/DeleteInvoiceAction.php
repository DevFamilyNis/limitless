<?php

declare(strict_types=1);

namespace App\Domain\Invoices\Actions;

use App\Domain\Invoices\DTO\DeleteInvoiceData;
use App\Models\Invoice;

final class DeleteInvoiceAction
{
    public function execute(DeleteInvoiceData $dto): void
    {
        $invoice = Invoice::query()
            ->findOrFail($dto->invoiceId);

        $invoice->delete();
    }
}
