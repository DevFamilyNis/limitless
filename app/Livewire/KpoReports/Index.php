<?php

namespace App\Livewire\KpoReports;

use App\Domain\Kpo\Actions\GenerateKpoReportPdfAction;
use App\Domain\Kpo\Actions\GenerateMonthlyKpoReportAction;
use App\Domain\Kpo\Actions\LockKpoReportAction;
use App\Domain\Kpo\DTO\GenerateKpoReportPdfData;
use App\Domain\Kpo\DTO\GenerateMonthlyKpoReportData;
use App\Domain\Kpo\DTO\LockKpoReportData;
use App\Domain\Kpo\Exceptions\LockedKpoReportException;
use App\Models\KpoReport;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class Index extends Component
{
    public int $year;

    public function mount(): void
    {
        $this->year = (int) now()->year;
    }

    public function generateReport(int $month): void
    {
        try {
            $report = app(GenerateMonthlyKpoReportAction::class)->execute(
                GenerateMonthlyKpoReportData::fromArray([
                    'user_id' => Auth::id(),
                    'year' => $this->year,
                    'month' => $month,
                ])
            );

            app(GenerateKpoReportPdfAction::class)->execute(
                GenerateKpoReportPdfData::fromArray([
                    'user_id' => Auth::id(),
                    'kpo_report_id' => $report->id,
                ])
            );

            session()->flash('status', 'KPO je uspešno generisan.');
        } catch (LockedKpoReportException $exception) {
            session()->flash('status', $exception->getMessage());
        }
    }

    public function lockReport(int $reportId): void
    {
        app(LockKpoReportAction::class)->execute(
            LockKpoReportData::fromArray([
                'user_id' => Auth::id(),
                'kpo_report_id' => $reportId,
            ])
        );

        session()->flash('status', 'KPO je zaključan i finalni PDF je generisan.');
    }

    public function downloadPdf(int $month): BinaryFileResponse
    {
        $report = KpoReport::query()
            ->where('user_id', Auth::id())
            ->where('year', $this->year)
            ->where('month', $month)
            ->first();

        if (! $report) {
            $report = app(GenerateMonthlyKpoReportAction::class)->execute(
                GenerateMonthlyKpoReportData::fromArray([
                    'user_id' => Auth::id(),
                    'year' => $this->year,
                    'month' => $month,
                ])
            );
        }

        $media = $report->getFirstMedia('pdf');

        if (! $media) {
            $media = app(GenerateKpoReportPdfAction::class)->execute(
                GenerateKpoReportPdfData::fromArray([
                    'user_id' => Auth::id(),
                    'kpo_report_id' => $report->id,
                ])
            );
        }

        return response()->download($media->getPath(), $media->file_name);
    }

    public function render(): View
    {
        $reports = KpoReport::query()
            ->where('user_id', Auth::id())
            ->where('year', $this->year)
            ->withCount('rows')
            ->with('media')
            ->get()
            ->keyBy('month');

        $years = KpoReport::query()
            ->where('user_id', Auth::id())
            ->select('year')
            ->distinct()
            ->orderByDesc('year')
            ->pluck('year')
            ->map(fn ($year): int => (int) $year)
            ->values();

        if (! $years->contains($this->year)) {
            $years = $years->prepend((int) $this->year)->unique()->sortDesc()->values();
        }

        $months = collect(range(1, 12))->map(function (int $month) use ($reports): array {
            $report = $reports->get($month);

            return [
                'month' => $month,
                'label' => Carbon::createFromDate($this->year, $month, 1)->translatedFormat('F'),
                'report' => $report,
                'is_locked' => $report?->locked_at !== null,
                'has_pdf' => $report?->getFirstMedia('pdf') !== null,
            ];
        });

        return view('livewire.kpo-reports.index', [
            'months' => $months,
            'years' => $years,
        ])->layout('layouts.app', [
            'title' => 'KPO izveštaji',
        ]);
    }
}
