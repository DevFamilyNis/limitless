<?php

declare(strict_types=1);

namespace App\Domain\Invoices\Actions;

use App\Domain\Invoices\DTO\GenerateInvoicePdfData;
use App\Domain\Invoices\Exceptions\InvoicePdfGenerationException;
use App\Models\Invoice;
use App\Models\UserSetting;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\File;
use Throwable;

final class GenerateInvoicePdfAction
{
    /**
     * @return array{path:string,filename:string}
     */
    public function execute(GenerateInvoicePdfData $dto): array
    {
        $invoice = Invoice::query()
            ->with(['client.user', 'client.type', 'client.person', 'client.company', 'status', 'items.project'])
            ->findOrFail($dto->invoiceId);

        $userSetting = UserSetting::query()
            ->first();

        if (! class_exists(Pdf::class)) {
            throw new InvoicePdfGenerationException('Dompdf nije instaliran. Pokreni: composer require barryvdh/laravel-dompdf');
        }

        $tmpDirectory = storage_path('app/tmp');
        File::ensureDirectoryExists($tmpDirectory);

        $fileName = sprintf('faktura-%s.pdf', str_replace('/', '-', (string) $invoice->invoice_number));
        $tmpPdfPath = $tmpDirectory.'/'.uniqid('invoice_', true).'.pdf';

        try {
            $pdfContent = Pdf::loadView('pdf.invoice', [
                'invoice' => $invoice,
                'userSetting' => $userSetting,
                'issuerEmail' => $invoice->client?->user?->email,
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

            throw new InvoicePdfGenerationException(
                sprintf('Neuspešno generisanje PDF-a za fakturu %s.', (string) $invoice->invoice_number),
                previous: $throwable
            );
        }
    }
}
