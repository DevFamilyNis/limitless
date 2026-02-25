<?php

use App\Domain\Kpo\Actions\GenerateMonthlyKpoReportAction;
use App\Domain\Kpo\DTO\GenerateMonthlyKpoReportData;
use App\Domain\Kpo\Exceptions\LockedKpoReportException;
use App\Models\Client;
use App\Models\ClientType;
use App\Models\Invoice;
use App\Models\InvoiceStatus;
use App\Models\KpoReport;
use App\Models\User;
use Illuminate\Support\Carbon;

test('generate monthly kpo report builds snapshot rows and totals from invoices created in month', function () {
    $user = User::factory()->create();
    $otherUser = User::factory()->create();

    $personTypeId = ClientType::query()->where('key', 'person')->value('id');
    $draftStatusId = InvoiceStatus::query()->where('key', 'draft')->value('id');

    $client = Client::query()->create([
        'user_id' => $user->id,
        'client_type_id' => $personTypeId,
        'display_name' => 'HR',
        'is_active' => true,
    ]);

    $otherClient = Client::query()->create([
        'user_id' => $otherUser->id,
        'client_type_id' => $personTypeId,
        'display_name' => 'Other',
        'is_active' => true,
    ]);

    $invoiceA = Invoice::query()->create([
        'client_id' => $client->id,
        'status_id' => $draftStatusId,
        'invoice_year' => 2026,
        'invoice_seq' => 2,
        'invoice_number' => '002/2026',
        'issue_date' => '2026-02-20',
        'due_date' => '2026-03-15',
        'subtotal' => 12000,
        'total' => 12000,
    ]);
    $invoiceA->forceFill(['created_at' => '2026-02-20 09:00:00', 'updated_at' => '2026-02-20 09:00:00'])->saveQuietly();

    $invoiceB = Invoice::query()->create([
        'client_id' => $client->id,
        'status_id' => $draftStatusId,
        'invoice_year' => 2026,
        'invoice_seq' => 1,
        'invoice_number' => '001/2026',
        'issue_date' => '2026-02-05',
        'due_date' => '2026-03-15',
        'subtotal' => 8000,
        'total' => 8000,
    ]);
    $invoiceB->forceFill(['created_at' => '2026-02-05 09:00:00', 'updated_at' => '2026-02-05 09:00:00'])->saveQuietly();

    $outsideMonth = Invoice::query()->create([
        'client_id' => $client->id,
        'status_id' => $draftStatusId,
        'invoice_year' => 2026,
        'invoice_seq' => 3,
        'invoice_number' => '003/2026',
        'issue_date' => '2026-03-01',
        'due_date' => '2026-03-15',
        'subtotal' => 5000,
        'total' => 5000,
    ]);
    $outsideMonth->forceFill(['created_at' => '2026-03-01 09:00:00', 'updated_at' => '2026-03-01 09:00:00'])->saveQuietly();

    $otherUserInvoice = Invoice::query()->create([
        'client_id' => $otherClient->id,
        'status_id' => $draftStatusId,
        'invoice_year' => 2026,
        'invoice_seq' => 101,
        'invoice_number' => '101/2026',
        'issue_date' => '2026-02-10',
        'due_date' => '2026-03-15',
        'subtotal' => 9999,
        'total' => 9999,
    ]);
    $otherUserInvoice->forceFill(['created_at' => '2026-02-10 09:00:00', 'updated_at' => '2026-02-10 09:00:00'])->saveQuietly();

    $report = app(GenerateMonthlyKpoReportAction::class)->execute(
        GenerateMonthlyKpoReportData::fromArray([
            'user_id' => $user->id,
            'year' => 2026,
            'month' => 2,
        ])
    );

    $report->load(['rows' => fn ($query) => $query->orderBy('row_no')]);

    expect($report->rows)->toHaveCount(3);
    expect((float) $report->products_total)->toBe(0.0);
    expect((float) $report->services_total)->toBe(29999.0);
    expect((float) $report->activity_total)->toBe(29999.0);

    expect($report->rows->pluck('row_no')->all())->toBe([1, 2, 3]);
    expect($report->rows->pluck('invoice_id')->all())->toBe([$invoiceB->id, $otherUserInvoice->id, $invoiceA->id]);
    expect($report->rows->pluck('entry_description')->all())->toBe([
        '001/2026 - HR',
        '101/2026 - Other',
        '002/2026 - HR',
    ]);

    $this->assertDatabaseMissing('kpo_report_rows', [
        'kpo_report_id' => $report->id,
        'invoice_id' => $outsideMonth->id,
    ]);

    $this->assertDatabaseHas('kpo_report_rows', [
        'kpo_report_id' => $report->id,
        'invoice_id' => $otherUserInvoice->id,
    ]);
});

test('generate monthly kpo report fails when report is locked', function () {
    $user = User::factory()->create();

    $report = KpoReport::query()->create([
        'user_id' => $user->id,
        'year' => 2026,
        'month' => 2,
        'period_from' => '2026-02-01',
        'period_to' => '2026-02-28',
        'products_total' => 0,
        'services_total' => 0,
        'activity_total' => 0,
        'currency' => 'RSD',
        'locked_at' => Carbon::parse('2026-02-28 23:59:59'),
    ]);

    expect(fn () => app(GenerateMonthlyKpoReportAction::class)->execute(
        GenerateMonthlyKpoReportData::fromArray([
            'user_id' => $user->id,
            'year' => 2026,
            'month' => 2,
        ])
    ))->toThrow(LockedKpoReportException::class);

    expect($report->fresh()->locked_at)->not()->toBeNull();
});
