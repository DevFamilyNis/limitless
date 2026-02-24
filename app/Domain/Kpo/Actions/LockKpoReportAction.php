<?php

declare(strict_types=1);

namespace App\Domain\Kpo\Actions;

use App\Domain\Kpo\DTO\GenerateKpoReportPdfData;
use App\Domain\Kpo\DTO\LockKpoReportData;
use App\Models\KpoReport;
use Illuminate\Support\Facades\DB;

final class LockKpoReportAction
{
    public function __construct(private readonly GenerateKpoReportPdfAction $generateKpoReportPdfAction) {}

    public function execute(LockKpoReportData $dto): KpoReport
    {
        $report = KpoReport::query()
            ->where('user_id', $dto->userId)
            ->findOrFail($dto->kpoReportId);

        DB::transaction(function () use ($report): void {
            if ($report->locked_at === null) {
                $report->update([
                    'locked_at' => now(),
                ]);
            }
        });

        $this->generateKpoReportPdfAction->execute(
            GenerateKpoReportPdfData::fromArray([
                'user_id' => $dto->userId,
                'kpo_report_id' => $report->id,
            ])
        );

        return $report->fresh();
    }
}
