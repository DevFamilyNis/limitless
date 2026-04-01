<?php

use App\Domain\Invoices\Actions\GenerateInvoicePdfAction;
use App\Domain\Invoices\Actions\GenerateInvoiceQrCodeAction;
use App\Domain\Invoices\DTO\GenerateInvoicePdfData;
use App\Models\Client;
use App\Models\ClientCompany;
use App\Models\ClientType;
use App\Models\Invoice;
use App\Models\InvoiceStatus;
use App\Models\Project;
use App\Models\User;
use App\Models\UserSetting;
use Illuminate\Support\Facades\File;

test('generate invoice pdf action creates temporary pdf file', function () {
    if (! class_exists(Barryvdh\DomPDF\Facade\Pdf::class)) {
        $this->markTestSkipped('barryvdh/laravel-dompdf nije instaliran u test okruženju.');
    }

    $user = User::factory()->create();

    $personTypeId = ClientType::query()->where('key', 'person')->value('id');
    $draftStatusId = InvoiceStatus::query()->where('key', 'draft')->value('id');

    $client = Client::query()->create([
        'user_id' => $user->id,
        'client_type_id' => $personTypeId,
        'display_name' => 'PDF Klijent',
        'is_active' => true,
    ]);

    $invoice = Invoice::query()->create([
        'client_id' => $client->id,
        'status_id' => $draftStatusId,
        'invoice_year' => 2026,
        'invoice_seq' => 7,
        'invoice_number' => '007/2026',
        'issue_date' => '2026-02-10',
        'issue_date_to' => '2026-02-28',
        'due_date' => '2026-02-15',
        'subtotal' => 5000,
        'total' => 5000,
    ]);

    $project = Project::factory()->create(['user_id' => $user->id]);

    $invoice->items()->create([
        'project_id' => $project->id,
        'client_project_rate_id' => null,
        'description' => 'Usluga',
        'quantity' => 1,
        'unit_price' => 5000,
        'amount' => 5000,
    ]);

    $result = app(GenerateInvoicePdfAction::class)->execute(
        GenerateInvoicePdfData::fromArray([
            'user_id' => $user->id,
            'invoice_id' => $invoice->id,
        ])
    );

    expect($result['filename'])->toBe('faktura-007-2026.pdf');
    expect(File::exists($result['path']))->toBeTrue();

    File::delete($result['path']);
});

test('generate invoice pdf action uses real company payer name for qr code', function () {
    if (! class_exists(Barryvdh\DomPDF\Facade\Pdf::class)) {
        $this->markTestSkipped('barryvdh/laravel-dompdf nije instaliran u test okruženju.');
    }

    $otherUser = User::factory()->create();
    $user = User::factory()->create();

    $companyTypeId = ClientType::query()->where('key', 'company')->value('id');
    $draftStatusId = InvoiceStatus::query()->where('key', 'draft')->value('id');

    $client = Client::query()->create([
        'user_id' => $user->id,
        'client_type_id' => $companyTypeId,
        'display_name' => 'Acme Logistics DOO',
        'address' => 'Bulevar Oslobodjenja 10, Novi Sad',
        'is_active' => true,
    ]);

    ClientCompany::query()->create([
        'client_id' => $client->id,
        'pib' => '123456789',
        'mb' => '12345678',
        'bank_account' => '160-0000000000000-00',
    ]);

    UserSetting::query()->create([
        'user_id' => $otherUser->id,
        'display_name' => 'Pogresan izdavalac',
        'address' => 'Pogresna 1, Beograd',
        'bank_account' => '160-1111111111111-11',
        'default_currency' => 'RSD',
    ]);

    UserSetting::query()->create([
        'user_id' => $user->id,
        'display_name' => 'Limitless DOO',
        'address' => 'Branka Radicevica 26a, Nis',
        'bank_account' => '160-0000000000000-00',
        'default_currency' => 'RSD',
    ]);

    $invoice = Invoice::query()->create([
        'client_id' => $client->id,
        'status_id' => $draftStatusId,
        'invoice_year' => 2026,
        'invoice_seq' => 8,
        'invoice_number' => '008/2026',
        'issue_date' => '2026-02-10',
        'issue_date_to' => '2026-02-28',
        'due_date' => '2026-02-15',
        'subtotal' => 5000,
        'total' => 5000,
    ]);

    $project = Project::factory()->create(['user_id' => $user->id]);

    $invoice->items()->create([
        'project_id' => $project->id,
        'client_project_rate_id' => null,
        'description' => 'Usluga',
        'quantity' => 1,
        'unit_price' => 5000,
        'amount' => 5000,
    ]);

    $fakeQrAction = new class
    {
        public ?string $issuerName = null;

        public ?string $payerDisplay = null;

        public function execute(
            string $issuerName,
            string $issuerBankAccount,
            string $invoiceTotal,
            string $invoiceNumber,
            string $payerDisplay,
            ?string $invoiceNote = null,
        ): ?string {
            $this->issuerName = $issuerName;
            $this->payerDisplay = $payerDisplay;

            return null;
        }
    };

    app()->instance(GenerateInvoiceQrCodeAction::class, $fakeQrAction);

    $result = app(GenerateInvoicePdfAction::class)->execute(
        GenerateInvoicePdfData::fromArray([
            'user_id' => $user->id,
            'invoice_id' => $invoice->id,
        ])
    );

    expect($fakeQrAction->issuerName)->toBe("Limitless DOO\nBranka Radicevica 26a\nNis");
    expect($fakeQrAction->payerDisplay)->toBe('Acme Logistics DOO');

    File::delete($result['path']);
});
