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

        $issuerName = (string) ($userSetting?->display_name ?? '');
        $issuerBank = (string) ($userSetting?->bank_account ?? '');

        // platilac = klijent (company/person)
        $payerDisplay = '';
        if ($invoice->client?->company) {
            $payerDisplay = (string) $invoice->client->company->company_name;
        } elseif ($invoice->client?->person) {
            $payerDisplay = trim((string) $invoice->client->person->first_name.' '.(string) $invoice->client->person->last_name);
        }

        $qrCodeDataUri = app(GenerateInvoiceQrCodeAction::class)->execute(
            issuerName: $issuerName,
            issuerBankAccount: $issuerBank,
            invoiceTotal: (string) $invoice->total,
            invoiceNumber: (string) $invoice->invoice_number,
            payerDisplay: $payerDisplay !== '' ? $payerDisplay : 'Klijent',
            invoiceNote: $invoice->note ?? null,
        );

        try {
            $pdfContent = Pdf::loadView('pdf.invoice', [
                'invoice' => $invoice,
                'userSetting' => $userSetting,
                'issuerEmail' => $invoice->client?->user?->email,

                // NOVO:
                'issuerName' => $issuerName,
                'issuerBank' => $issuerBank,
                'qrCodeDataUri' => $qrCodeDataUri,
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
