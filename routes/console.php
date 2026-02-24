<?php

use App\Domain\Kpo\Actions\GenerateKpoReportPdfAction;
use App\Domain\Kpo\Actions\GenerateMonthlyKpoReportAction;
use App\Domain\Kpo\DTO\GenerateKpoReportPdfData;
use App\Domain\Kpo\DTO\GenerateMonthlyKpoReportData;
use App\Domain\Kpo\Exceptions\LockedKpoReportException;
use App\Models\User;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Schedule::call(function (): void {
    if (! now()->isLastOfMonth()) {
        return;
    }

    $year = (int) now()->year;
    $month = (int) now()->month;

    User::query()->select('id')->orderBy('id')->each(function (User $user) use ($year, $month): void {
        try {
            $report = app(GenerateMonthlyKpoReportAction::class)->execute(
                GenerateMonthlyKpoReportData::fromArray([
                    'user_id' => $user->id,
                    'year' => $year,
                    'month' => $month,
                ])
            );

            app(GenerateKpoReportPdfAction::class)->execute(
                GenerateKpoReportPdfData::fromArray([
                    'user_id' => $user->id,
                    'kpo_report_id' => $report->id,
                ])
            );
        } catch (LockedKpoReportException) {
            // Locked report remains unchanged by scheduler.
        }
    });
})
    ->dailyAt('23:55')
    ->name('kpo:generate-monthly');
