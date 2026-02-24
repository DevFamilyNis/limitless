<?php

declare(strict_types=1);

namespace App\Domain\Kpo\Actions;

use App\Domain\Kpo\DTO\GenerateKpoReportPdfData;
use App\Domain\Kpo\Exceptions\KpoReportPdfGenerationException;
use App\Models\KpoReport;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\File;
use Spatie\MediaLibrary\MediaCollections\Models\Media;
use Throwable;

final class GenerateKpoReportPdfAction
{
    public function execute(GenerateKpoReportPdfData $dto): Media
    {
        $report = KpoReport::query()
            ->with([
                'user',
                'rows' => fn ($query) => $query->orderBy('row_no'),
            ])
            ->where('user_id', $dto->userId)
            ->findOrFail($dto->kpoReportId);

        if (! class_exists(Pdf::class)) {
            throw new KpoReportPdfGenerationException('Dompdf nije instaliran. Pokreni: composer require barryvdh/laravel-dompdf');
        }

        $tmpDirectory = storage_path('app/tmp');
        File::ensureDirectoryExists($tmpDirectory);

        $tmpPdfPath = sprintf(
            '%s/kpo-%d-%02d-%s.pdf',
            $tmpDirectory,
            $report->year,
            $report->month,
            uniqid()
        );

        try {
            $pdfContent = Pdf::loadView('pdf.kpo-report', [
                'report' => $report,
                'rows' => $report->rows,
            ])
                ->setPaper('a4', 'portrait')
                ->output();

            File::put($tmpPdfPath, $pdfContent);

            $report->clearMediaCollection('pdf');

            return $report->addMedia($tmpPdfPath)
                ->usingFileName(sprintf('kpo-%d-%02d.pdf', $report->year, $report->month))
                ->toMediaCollection('pdf');
        } catch (Throwable $throwable) {
            throw new KpoReportPdfGenerationException(
                sprintf('Neuspešno generisanje KPO PDF-a za %02d/%d.', $report->month, $report->year),
                previous: $throwable
            );
        } finally {
            File::delete($tmpPdfPath);
        }
    }
}
