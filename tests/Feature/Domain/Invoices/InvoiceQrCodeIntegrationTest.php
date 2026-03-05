<?php

use App\Domain\Invoices\Actions\GenerateInvoiceQrCodeAction;
use App\Infrastructure\Qr\QrCodeGenerator;
use App\Support\IpsQrPayload;
use Carbon\Carbon;
use Illuminate\Support\Collection;

function fakeInvoiceForPdfView(): object
{
    $clientType = new stdClass;
    $clientType->key = 'company';

    $clientCompany = new stdClass;
    $clientCompany->mb = '66579484';
    $clientCompany->pib = '113101530';

    $client = new stdClass;
    $client->display_name = 'QR Test Klijent';
    $client->type = $clientType;
    $client->person = null;
    $client->company = $clientCompany;
    $client->address = 'Test adresa 1';

    $invoice = new stdClass;
    $invoice->invoice_number = 'FIZ-000123/2026';
    $invoice->created_at = Carbon::parse('2026-03-01');
    $invoice->issue_date = Carbon::parse('2026-03-01');
    $invoice->issue_date_to = Carbon::parse('2026-03-01');
    $invoice->due_date = Carbon::parse('2026-03-15');
    $invoice->total = '12500.00';
    $invoice->note = 'Placanje usluge odrzavanja';
    $invoice->items = new Collection;
    $invoice->client = $client;

    return $invoice;
}

function fakeUserSettingForPdfView(): object
{
    $userSetting = new stdClass;
    $userSetting->display_name = 'Test Firma DOO';
    $userSetting->address = 'Branka Radicevica 26a';
    $userSetting->pib = '113101530';
    $userSetting->mb = '66579484';
    $userSetting->bank_account = '160-0000000000000-00';
    $userSetting->default_currency = 'RSD';

    return $userSetting;
}

test('ips payload builder returns expected base format with normalized values', function () {
    $payload = IpsQrPayload::make([
        'racun' => '160-0000000000000-00',
        'primalac' => 'Test Firma DOO',
        'iznos' => '12500,00',
        'platilac' => 'Kupac Test',
        'sifra' => 221,
        'svrha' => 'Placanje po fakturi FIZ-000123/2026',
    ]);

    expect($payload)->toContain('K:PR|V:01|C:1');
    expect($payload)->toContain('|R:160000000000000000');
    expect($payload)->toContain('|I:RSD12500,00');
    expect($payload)->toContain('|SF:221');
    expect($payload)->toContain('|S:Placanje po fakturi FIZ-000123/2026');
});

test('qr code generator returns png data uri', function () {
    $generator = app(QrCodeGenerator::class);

    $output = $generator->generate('K:PR|V:01|C:1|R:160000000000000000|N:Test|I:RSD1,00|P:Kupac|SF:221|S:Test');

    expect($output)->toStartWith('data:image/png;base64,');
    expect(strlen($output))->toBeGreaterThan(100);
});

test('generate invoice qr action returns data uri for valid data', function () {
    $output = app(GenerateInvoiceQrCodeAction::class)->execute(
        issuerName: 'Test Firma DOO',
        issuerBankAccount: '160-0000000000000-00',
        invoiceTotal: '12500.00',
        invoiceNumber: 'FIZ-000123/2026',
        payerDisplay: 'Kupac Test',
        invoiceNote: 'Placanje po fakturi',
    );

    expect($output)->not()->toBeNull();
    expect($output)->toStartWith('data:image/png;base64,');
});

test('generate invoice qr action returns null when required data is missing', function () {
    $output = app(GenerateInvoiceQrCodeAction::class)->execute(
        issuerName: '',
        issuerBankAccount: '160-0000000000000-00',
        invoiceTotal: '12500.00',
        invoiceNumber: 'FIZ-000123/2026',
        payerDisplay: 'Kupac Test',
        invoiceNote: null,
    );

    expect($output)->toBeNull();
});

test('invoice pdf blade renders qr image when qr data is present', function () {
    $html = view('pdf.invoice', [
        'invoice' => fakeInvoiceForPdfView(),
        'userSetting' => fakeUserSettingForPdfView(),
        'issuerEmail' => 'issuer@example.com',
        'qrCodeDataUri' => 'data:image/png;base64,TEST123',
    ])->render();

    expect($html)->toContain('alt="IPS QR"');
    expect($html)->toContain('data:image/png;base64,TEST123');
});

test('invoice pdf blade does not render qr image when qr data is missing', function () {
    $html = view('pdf.invoice', [
        'invoice' => fakeInvoiceForPdfView(),
        'userSetting' => fakeUserSettingForPdfView(),
        'issuerEmail' => 'issuer@example.com',
        'qrCodeDataUri' => null,
    ])->render();

    expect($html)->not()->toContain('alt="IPS QR"');
});
