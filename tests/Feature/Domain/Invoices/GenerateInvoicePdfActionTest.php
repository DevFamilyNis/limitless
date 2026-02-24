<?php

use App\Domain\Invoices\Actions\GenerateInvoicePdfAction;
use App\Domain\Invoices\DTO\GenerateInvoicePdfData;
use App\Models\Client;
use App\Models\ClientType;
use App\Models\Invoice;
use App\Models\InvoiceStatus;
use App\Models\Project;
use App\Models\User;
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
