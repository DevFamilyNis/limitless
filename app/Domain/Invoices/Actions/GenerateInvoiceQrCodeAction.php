<?php

declare(strict_types=1);

namespace App\Domain\Invoices\Actions;

use App\Infrastructure\Qr\QrCodeGenerator;
use App\Support\IpsQrPayload;
use Illuminate\Support\Facades\Log;

final class GenerateInvoiceQrCodeAction
{
    public function __construct(
        private readonly QrCodeGenerator $qr,
    ) {}

    public function execute(
        string $issuerName,
        string $issuerBankAccount,
        string $invoiceTotal,
        string $invoiceNumber,
        string $payerDisplay,
        ?string $invoiceNote = null,
    ): ?string {
        $issuerName = trim($issuerName);
        $payerDisplay = trim($payerDisplay);

        // bank account must be 18 digits for IPS QR to be accepted in practice
        $issuerBankDigits = preg_replace('/\D/', '', $issuerBankAccount) ?? '';

        if ($issuerName === '' || $payerDisplay === '' || $issuerBankDigits === '' || trim($invoiceTotal) === '') {
            return null;
        }

        if (! preg_match('/^\d{18}$/', $issuerBankDigits)) {
            return null;
        }

        // Amount -> "1234,56"
        $normalizedTotal = str_replace(',', '.', trim($invoiceTotal));
        if ($normalizedTotal === '' || ! is_numeric($normalizedTotal)) {
            return null;
        }

        $amount = number_format((float) $normalizedTotal, 2, ',', '');
        if ($amount === '0,00') {
            return null;
        }

        // Purpose must be <= 35 chars for best compatibility
        // (you said clients use exactly this format)
        $svrha = 'Placanje po fakturi '.trim($invoiceNumber);
        $svrha = mb_substr($svrha, 0, 35);

        // Optional: if you want to prefer invoice note, keep it BUT still cap to 35:
        // if ($invoiceNote && trim($invoiceNote) !== '') {
        //     $svrha = mb_substr(trim($invoiceNote), 0, 35);
        // }

        try {
            $payload = IpsQrPayload::make([
                'racun' => $issuerBankDigits,
                'primalac' => $issuerName,
                'iznos' => $amount,
                'platilac' => $payerDisplay,
                'sifra' => 221,
                'svrha' => $svrha,

                // IMPORTANT: no 'poziv' => ... (no RO)
            ]);

            return $this->qr->generate($payload);
        } catch (\Throwable $e) {
            Log::warning('Invoice IPS QR generation failed', [
                'invoice_number' => $invoiceNumber,
                'error' => $e->getMessage(),
            ]);

            return null;
        }
    }
}
