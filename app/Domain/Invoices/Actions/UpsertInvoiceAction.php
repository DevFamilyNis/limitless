<?php

declare(strict_types=1);

namespace App\Domain\Invoices\Actions;

use App\Actions\Invoices\GenerateInvoiceNumber;
use App\Domain\Invoices\DTO\UpsertInvoiceData;
use App\Models\Client;
use App\Models\ClientProjectRate;
use App\Models\Invoice;
use App\Models\InvoiceStatus;
use App\Models\Project;
use Illuminate\Support\Facades\DB;

final class UpsertInvoiceAction
{
    public function __construct(private readonly GenerateInvoiceNumber $generateInvoiceNumber) {}

    public function execute(UpsertInvoiceData $dto): Invoice
    {
        $client = Client::query()
            ->with('type')
            ->findOrFail($dto->clientId);

        $status = InvoiceStatus::query()->findOrFail($dto->statusId);

        $invoice = $dto->invoiceId
            ? Invoice::query()
                ->with('items')
                ->findOrFail($dto->invoiceId)
            : new Invoice;

        $items = collect($dto->items);
        $projectIds = $items
            ->pluck('projectId')
            ->map(fn ($id): int => (int) $id)
            ->values();

        $allowedProjectIds = Project::query()
            ->whereIn('id', $projectIds)
            ->pluck('id')
            ->map(fn (int $projectId): int => (int) $projectId)
            ->all();

        if ($projectIds->diff($allowedProjectIds)->isNotEmpty()) {
            abort(403);
        }

        $rateIds = $items
            ->pluck('clientProjectRateId')
            ->filter(fn ($rateId): bool => $rateId !== null && $rateId !== '')
            ->map(fn ($rateId): int => (int) $rateId)
            ->values();

        if ($rateIds->isNotEmpty()) {
            $allowedRateIds = ClientProjectRate::query()
                ->where('client_id', $client->id)
                ->whereIn('project_id', $projectIds)
                ->whereIn('id', $rateIds)
                ->pluck('id')
                ->map(fn (int $rateId): int => (int) $rateId)
                ->all();

            if ($rateIds->diff($allowedRateIds)->isNotEmpty()) {
                abort(403);
            }
        }

        DB::transaction(function () use ($client, $status, $invoice, $dto, $items): void {
            if (! $invoice->exists) {
                $generated = $client->type?->key === 'company'
                    ? $this->generateInvoiceNumber->execute()
                    : $this->generateAuxiliaryInvoiceNumber();
                $invoice->invoice_year = $generated['invoice_year'];
                $invoice->invoice_seq = $generated['invoice_seq'];
                $invoice->invoice_number = $generated['invoice_number'];
            }

            $invoice->fill([
                'client_id' => $client->id,
                'status_id' => $status->id,
                'issue_date' => $dto->issueDate,
                'issue_date_to' => $dto->issueDateTo,
                'due_date' => $dto->dueDate,
                'subtotal' => $dto->total,
                'total' => $dto->total,
                'note' => $dto->note,
            ]);

            $invoice->save();
            $invoice->items()->delete();

            $invoice->items()->createMany(
                $items->map(fn (array $item): array => [
                    'project_id' => (int) $item['projectId'],
                    'client_project_rate_id' => $item['clientProjectRateId'] !== '' ? (int) $item['clientProjectRateId'] : null,
                    'description' => $item['description'],
                    'quantity' => $item['quantity'],
                    'unit_price' => $item['unitPrice'],
                    'amount' => $item['amount'],
                ])->all()
            );
        });

        return $invoice->refresh();
    }

    /**
     * @return array{invoice_year:int,invoice_seq:int,invoice_number:string}
     */
    public function preview(?int $invoiceYear = null): array
    {
        return $this->generateInvoiceNumber->preview($invoiceYear);
    }

    /**
     * @return array{invoice_year:int,invoice_seq:int,invoice_number:string}
     */
    private function generateAuxiliaryInvoiceNumber(): array
    {
        return DB::transaction(function (): array {
            $auxiliaryYear = 0;
            $lastSequence = (int) Invoice::query()
                ->where('invoice_year', $auxiliaryYear)
                ->lockForUpdate()
                ->max('invoice_seq');

            $nextSequence = $lastSequence + 1;
            $displayYear = (int) now()->year;

            return [
                'invoice_year' => $auxiliaryYear,
                'invoice_seq' => $nextSequence,
                'invoice_number' => sprintf('FIZ-%06d/%d', $nextSequence, $displayYear),
            ];
        });
    }
}
