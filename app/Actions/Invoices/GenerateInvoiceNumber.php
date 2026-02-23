<?php

declare(strict_types=1);

namespace App\Actions\Invoices;

use App\Models\Invoice;
use Illuminate\Support\Facades\DB;

class GenerateInvoiceNumber
{
    /**
     * @return array{invoice_year:int,invoice_seq:int,invoice_number:string}
     */
    public function preview(?int $invoiceYear = null): array
    {
        $year = $invoiceYear ?? (int) now()->year;

        $nextSequence = ((int) Invoice::query()
            ->where('invoice_year', $year)
            ->max('invoice_seq')) + 1;

        return [
            'invoice_year' => $year,
            'invoice_seq' => $nextSequence,
            'invoice_number' => $this->format($nextSequence, $year),
        ];
    }

    /**
     * @return array{invoice_year:int,invoice_seq:int,invoice_number:string}
     */
    public function execute(?int $invoiceYear = null): array
    {
        $year = $invoiceYear ?? (int) now()->year;

        return DB::transaction(function () use ($year): array {
            $lastSequence = (int) Invoice::query()
                ->where('invoice_year', $year)
                ->lockForUpdate()
                ->max('invoice_seq');

            $nextSequence = $lastSequence + 1;

            return [
                'invoice_year' => $year,
                'invoice_seq' => $nextSequence,
                'invoice_number' => $this->format($nextSequence, $year),
            ];
        });
    }

    public function format(int $sequence, int $year): string
    {
        return sprintf('%03d/%d', $sequence, $year);
    }
}
