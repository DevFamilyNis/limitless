<?php

declare(strict_types=1);

namespace App\Domain\WorkSessions\Actions;

use App\Domain\WorkSessions\DTO\GenerateWorkSessionReportData;
use App\Domain\WorkSessions\Exceptions\WorkSessionReportPdfException;
use App\Models\User;
use App\Models\WorkSession;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\File;
use Throwable;

final class GenerateWorkSessionReportPdfAction
{
    /**
     * @return array{path: string, filename: string}
     */
    public function execute(GenerateWorkSessionReportData $dto): array
    {
        $sessions = WorkSession::query()
            ->with('user')
            ->when($dto->userId !== null, fn ($q) => $q->where('user_id', $dto->userId))
            ->whereBetween('work_date', [$dto->dateFrom->toDateString(), $dto->dateTo->toDateString()])
            ->orderBy('work_date')
            ->orderBy('started_at')
            ->get();

        $userName = $dto->userId !== null
            ? (User::query()->find($dto->userId)?->name ?? 'Nepoznat korisnik')
            : null;

        $totalMinutes = $sessions->sum('duration_minutes');

        $tmpDirectory = storage_path('app/tmp');
        File::ensureDirectoryExists($tmpDirectory);

        $slug = $dto->userId !== null ? 'korisnik-'.$dto->userId : 'svi';
        $fileName = sprintf(
            'radne-sesije-%s-%s-%s.pdf',
            $slug,
            $dto->dateFrom->format('Y-m-d'),
            $dto->dateTo->format('Y-m-d'),
        );
        $tmpPdfPath = $tmpDirectory.'/'.uniqid('work_sessions_', true).'.pdf';

        try {
            $pdfContent = Pdf::loadView('pdf.work-session-report', [
                'sessions' => $sessions,
                'userName' => $userName,
                'dateFrom' => $dto->dateFrom,
                'dateTo' => $dto->dateTo,
                'totalMinutes' => $totalMinutes,
            ])
                ->setPaper('a4', 'portrait')
                ->output();

            File::put($tmpPdfPath, $pdfContent);

            return [
                'path' => $tmpPdfPath,
                'filename' => $fileName,
            ];
        } catch (Throwable $throwable) {
            File::delete($tmpPdfPath);

            throw new WorkSessionReportPdfException(
                'Neuspešno generisanje PDF izveštaja radnih sesija.',
                previous: $throwable,
            );
        }
    }
}
