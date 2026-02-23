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
            ->whereHas('client', fn ($query) => $query->where('user_id', $dto->userId))
            ->findOrFail($dto->invoiceId);

        $invoice->delete();
    }
}
