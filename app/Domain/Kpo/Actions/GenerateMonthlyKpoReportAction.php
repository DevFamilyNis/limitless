<?php

declare(strict_types=1);

namespace App\Domain\Kpo\Actions;

use App\Domain\Kpo\DTO\GenerateMonthlyKpoReportData;
use App\Domain\Kpo\Exceptions\LockedKpoReportException;
use App\Models\Invoice;
use App\Models\KpoReport;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

final class GenerateMonthlyKpoReportAction
{
    public function execute(GenerateMonthlyKpoReportData $dto): KpoReport
    {
        $periodFrom = Carbon::createFromDate($dto->year, $dto->month, 1)->startOfMonth();
        $periodTo = (clone $periodFrom)->endOfMonth();

        $report = KpoReport::query()->firstOrCreate(
            [
                'user_id' => $dto->userId,
                'year' => $dto->year,
                'month' => $dto->month,
            ],
            [
                'period_from' => $periodFrom->toDateString(),
                'period_to' => $periodTo->toDateString(),
                'products_total' => 0,
                'services_total' => 0,
                'activity_total' => 0,
                'currency' => 'RSD',
            ]
        );

        if ($report->locked_at !== null) {
            throw LockedKpoReportException::forPeriod($dto->year, $dto->month);
        }

        $invoices = Invoice::query()
            ->with('client')
            ->whereHas('client', fn ($query) => $query->where('user_id', $dto->userId))
            ->whereBetween('created_at', [$periodFrom->copy()->startOfDay(), $periodTo->copy()->endOfDay()])
            ->orderBy('issue_date')
            ->orderBy('invoice_number')
            ->orderBy('id')
            ->get();

        $servicesTotal = (float) $invoices->sum(fn (Invoice $invoice): float => (float) $invoice->total);

        DB::transaction(function () use ($report, $periodFrom, $periodTo, $invoices, $servicesTotal): void {
            $report->update([
                'period_from' => $periodFrom->toDateString(),
                'period_to' => $periodTo->toDateString(),
                'products_total' => 0,
                'services_total' => $servicesTotal,
                'activity_total' => $servicesTotal,
                'currency' => 'RSD',
            ]);

            $report->rows()->delete();

            $rowNumber = 1;
            $rows = $invoices->map(function (Invoice $invoice) use ($report, &$rowNumber): array {
                $entryDescription = trim(sprintf('%s - %s', (string) $invoice->invoice_number, (string) $invoice->client?->display_name));

                return [
                    'kpo_report_id' => $report->id,
                    'invoice_id' => $invoice->id,
                    'entry_date' => $invoice->issue_date?->toDateString() ?? $invoice->created_at->toDateString(),
                    'entry_description' => $entryDescription,
                    'products_amount' => 0,
                    'services_amount' => (float) $invoice->total,
                    'activity_amount' => (float) $invoice->total,
                    'row_no' => $rowNumber++,
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            })->all();

            if ($rows !== []) {
                $report->rows()->insert($rows);
            }
        });

        return $report->fresh(['rows' => fn ($query) => $query->orderBy('row_no')]);
    }
}
