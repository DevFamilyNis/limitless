<?php

declare(strict_types=1);

namespace App\Domain\Invoices\Actions;

use App\Domain\Invoices\DTO\MarkInvoicePaidData;
use App\Domain\Transactions\Actions\ResolveInvoiceIncomeCategoryAction;
use App\Models\Invoice;
use App\Models\InvoiceStatus;
use App\Models\Transaction;
use Illuminate\Support\Facades\DB;

final class MarkInvoicePaidAction
{
    public function execute(MarkInvoicePaidData $dto): Invoice
    {
        $paidStatusId = InvoiceStatus::query()->where('key', 'paid')->value('id');

        if ($paidStatusId === null) {
            abort(422, 'Paid status missing.');
        }

        $invoice = Invoice::query()
            ->with('client')
            ->findOrFail($dto->invoiceId);

        if ((int) $invoice->client?->user_id !== $dto->userId) {
            abort(403);
        }

        DB::transaction(function () use ($dto, $invoice, $paidStatusId): void {
            $invoice->update(['status_id' => $paidStatusId]);

            $invoiceIncomeCategory = app(ResolveInvoiceIncomeCategoryAction::class)->execute($dto->userId);

            $alreadyBooked = Transaction::query()
                ->where('invoice_id', $invoice->id)
                ->first();

            if ($alreadyBooked) {
                $alreadyBooked->update(['category_id' => $invoiceIncomeCategory->id]);

                return;
            }

            Transaction::query()->create([
                'user_id' => $dto->userId,
                'category_id' => $invoiceIncomeCategory->id,
                'client_id' => $invoice->client_id,
                'invoice_id' => $invoice->id,
                'date' => now()->toDateString(),
                'amount' => (float) $invoice->total,
                'currency' => 'RSD',
                'title' => sprintf('Naplata %s', (string) $invoice->invoice_number),
                'note' => null,
            ]);
        });

        return $invoice->refresh();
    }
}
