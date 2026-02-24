<?php

use App\Domain\Kpo\Actions\GenerateKpoReportPdfAction;
use App\Domain\Kpo\DTO\GenerateKpoReportPdfData;
use App\Models\Client;
use App\Models\ClientType;
use App\Models\Invoice;
use App\Models\InvoiceStatus;
use App\Models\KpoReport;
use App\Models\User;
use Illuminate\Support\Facades\Storage;

test('generate kpo report pdf action stores a single media file in pdf collection', function () {
    if (! class_exists(Barryvdh\DomPDF\Facade\Pdf::class)) {
        $this->markTestSkipped('barryvdh/laravel-dompdf nije instaliran u test okruženju.');
    }

    Storage::fake('public');
    config()->set('media-library.disk_name', 'public');

    $user = User::factory()->create();

    $personTypeId = ClientType::query()->where('key', 'person')->value('id');
    $draftStatusId = InvoiceStatus::query()->where('key', 'draft')->value('id');

    $client = Client::query()->create([
        'user_id' => $user->id,
        'client_type_id' => $personTypeId,
        'display_name' => 'HR',
        'is_active' => true,
    ]);

    $invoice = Invoice::query()->create([
        'client_id' => $client->id,
        'status_id' => $draftStatusId,
        'invoice_year' => 2026,
        'invoice_seq' => 1,
        'invoice_number' => '001/2026',
        'issue_date' => '2026-02-10',
        'due_date' => '2026-03-15',
        'subtotal' => 10000,
        'total' => 10000,
    ]);

    $report = KpoReport::query()->create([
        'user_id' => $user->id,
        'year' => 2026,
        'month' => 2,
        'period_from' => '2026-02-01',
        'period_to' => '2026-02-28',
        'products_total' => 0,
        'services_total' => 10000,
        'activity_total' => 10000,
        'currency' => 'RSD',
    ]);

    $report->rows()->create([
        'invoice_id' => $invoice->id,
        'entry_date' => '2026-02-10',
        'entry_description' => '001/2026 - HR',
        'products_amount' => 0,
        'services_amount' => 10000,
        'activity_amount' => 10000,
        'row_no' => 1,
    ]);

    app(GenerateKpoReportPdfAction::class)->execute(
        GenerateKpoReportPdfData::fromArray([
            'user_id' => $user->id,
            'kpo_report_id' => $report->id,
        ])
    );

    $report->refresh();
    $mediaItems = $report->getMedia('pdf');

    expect($mediaItems)->toHaveCount(1);

    $media = $mediaItems->first();
    expect($media)->not()->toBeNull();
    expect($media->file_name)->toBe('kpo-2026-02.pdf');

    Storage::disk($media->disk)->assertExists($media->getPathRelativeToRoot());
});
