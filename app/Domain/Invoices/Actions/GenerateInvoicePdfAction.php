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
            ->with(['client.user.setting', 'client.type', 'client.person', 'client.company', 'status', 'items.project'])
            ->findOrFail($dto->invoiceId);

        $userSetting = $invoice->client?->user?->setting;

        if (! class_exists(Pdf::class)) {
            throw new InvoicePdfGenerationException('Dompdf nije instaliran. Pokreni: composer require barryvdh/laravel-dompdf');
        }

        $tmpDirectory = storage_path('app/tmp');
        File::ensureDirectoryExists($tmpDirectory);

        $fileName = sprintf('faktura-%s.pdf', str_replace('/', '-', (string) $invoice->invoice_number));
        $tmpPdfPath = $tmpDirectory.'/'.uniqid('invoice_', true).'.pdf';

        $issuerName = $this->resolveIssuerDisplay($invoice, $userSetting);
        $issuerBank = (string) ($userSetting?->bank_account ?? '');

        $payerDisplay = $this->resolvePayerDisplay($invoice);

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

    private function resolvePayerDisplay(Invoice $invoice): string
    {
        if ($invoice->client?->person) {
            $personDisplay = trim((string) $invoice->client->person->first_name.' '.(string) $invoice->client->person->last_name);

            if ($personDisplay !== '') {
                return $personDisplay;
            }
        }

        $clientDisplay = trim((string) ($invoice->client?->display_name ?? ''));

        if ($clientDisplay !== '') {
            return $clientDisplay;
        }

        return 'Klijent';
    }

    private function resolveIssuerDisplay(Invoice $invoice, ?UserSetting $userSetting): string
    {
        $issuerDisplay = trim((string) ($userSetting?->display_name ?? ''));

        if ($issuerDisplay === '') {
            $issuerDisplay = trim((string) ($invoice->client?->user?->name ?? ''));
        }

        return $this->formatQrPartyDisplay($issuerDisplay, (string) ($userSetting?->address ?? ''));
    }

    private function formatQrPartyDisplay(string $display, string $address): string
    {
        $lines = [trim($display)];

        [$addressLine, $cityLine] = $this->splitAddress($address);

        if ($addressLine !== null) {
            $lines[] = $addressLine;
        }

        if ($cityLine !== null) {
            $lines[] = $cityLine;
        }

        return implode("\n", array_values(array_filter($lines, static fn (?string $line): bool => $line !== null && $line !== '')));
    }

    /**
     * @return array{0:?string,1:?string}
     */
    private function splitAddress(string $address): array
    {
        $normalizedAddress = trim(preg_replace("/\r\n|\r|\n/", "\n", $address) ?? '');

        if ($normalizedAddress === '') {
            return [null, null];
        }

        $addressLines = array_values(array_filter(array_map('trim', explode("\n", $normalizedAddress)), static fn (string $line): bool => $line !== ''));

        if (count($addressLines) >= 2) {
            return [$addressLines[0], $addressLines[1]];
        }

        $parts = array_values(array_filter(array_map('trim', explode(',', $normalizedAddress)), static fn (string $part): bool => $part !== ''));

        if (count($parts) >= 2) {
            $city = array_pop($parts);
            $street = implode(', ', $parts);

            return [$street !== '' ? $street : null, $city !== '' ? $city : null];
        }

        return [$normalizedAddress, null];
    }
}
