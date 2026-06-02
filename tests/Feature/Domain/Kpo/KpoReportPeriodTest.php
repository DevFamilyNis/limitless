<?php

use App\Domain\Kpo\Actions\GenerateMonthlyKpoReportAction;
use App\Domain\Kpo\DTO\GenerateMonthlyKpoReportData;
use App\Models\Client;
use App\Models\ClientType;
use App\Models\Invoice;
use App\Models\InvoiceStatus;
use App\Models\User;

// Poslovno pravilo: KPO koristi created_at (datum knjiženja), ne issue_date.
// KPO za januar hvata fakture kreirane u januaru bez obzira na period usluge.

test('kpo report includes invoices created in the target month', function () {
    $user = User::factory()->create();
    $personTypeId = ClientType::query()->where('key', 'person')->value('id');
    $draftStatusId = InvoiceStatus::query()->where('key', 'draft')->value('id');

    $client = Client::query()->create([
        'user_id' => $user->id,
        'client_type_id' => $personTypeId,
        'display_name' => 'KPO Test Client',
        'is_active' => true,
    ]);

    $invoice = Invoice::query()->create([
        'client_id' => $client->id,
        'status_id' => $draftStatusId,
        'invoice_year' => 2026,
        'invoice_seq' => 1,
        'invoice_number' => '001/2026',
        'issue_date' => '2026-01-01',
        'issue_date_to' => '2026-01-31',
        'subtotal' => 10000,
        'total' => 10000,
    ]);
    $invoice->forceFill(['created_at' => '2026-01-15 10:00:00', 'updated_at' => '2026-01-15 10:00:00'])->saveQuietly();

    $report = app(GenerateMonthlyKpoReportAction::class)->execute(
        GenerateMonthlyKpoReportData::fromArray([
            'user_id' => $user->id,
            'year' => 2026,
            'month' => 1,
        ])
    );

    expect($report->rows)->toHaveCount(1);
    expect((float) $report->services_total)->toBe(10000.0);
});

test('kpo report excludes invoices created in different month even when issue date is in period', function () {
    $user = User::factory()->create();
    $personTypeId = ClientType::query()->where('key', 'person')->value('id');
    $draftStatusId = InvoiceStatus::query()->where('key', 'draft')->value('id');

    $client = Client::query()->create([
        'user_id' => $user->id,
        'client_type_id' => $personTypeId,
        'display_name' => 'KPO Exclude Client',
        'is_active' => true,
    ]);

    // Faktura za januar uslugu, ali kreirana u februaru — ne sme ući u januarski KPO
    $invoice = Invoice::query()->create([
        'client_id' => $client->id,
        'status_id' => $draftStatusId,
        'invoice_year' => 2026,
        'invoice_seq' => 2,
        'invoice_number' => '002/2026',
        'issue_date' => '2026-01-01',
        'issue_date_to' => '2026-01-31',
        'subtotal' => 8000,
        'total' => 8000,
    ]);
    $invoice->forceFill(['created_at' => '2026-02-05 10:00:00', 'updated_at' => '2026-02-05 10:00:00'])->saveQuietly();

    $januaryReport = app(GenerateMonthlyKpoReportAction::class)->execute(
        GenerateMonthlyKpoReportData::fromArray([
            'user_id' => $user->id,
            'year' => 2026,
            'month' => 1,
        ])
    );

    expect($januaryReport->rows)->toHaveCount(0);
    expect((float) $januaryReport->services_total)->toBe(0.0);
});

test('kpo report includes invoice created on first day of month', function () {
    $user = User::factory()->create();
    $personTypeId = ClientType::query()->where('key', 'person')->value('id');
    $draftStatusId = InvoiceStatus::query()->where('key', 'draft')->value('id');

    $client = Client::query()->create([
        'user_id' => $user->id,
        'client_type_id' => $personTypeId,
        'display_name' => 'KPO Boundary Client',
        'is_active' => true,
    ]);

    $invoice = Invoice::query()->create([
        'client_id' => $client->id,
        'status_id' => $draftStatusId,
        'invoice_year' => 2026,
        'invoice_seq' => 3,
        'invoice_number' => '003/2026',
        'issue_date' => '2026-03-01',
        'issue_date_to' => '2026-03-31',
        'subtotal' => 5000,
        'total' => 5000,
    ]);
    $invoice->forceFill(['created_at' => '2026-03-01 00:00:01', 'updated_at' => '2026-03-01 00:00:01'])->saveQuietly();

    $report = app(GenerateMonthlyKpoReportAction::class)->execute(
        GenerateMonthlyKpoReportData::fromArray([
            'user_id' => $user->id,
            'year' => 2026,
            'month' => 3,
        ])
    );

    expect($report->rows)->toHaveCount(1);
});

test('kpo report includes invoice created on last day of month', function () {
    $user = User::factory()->create();
    $personTypeId = ClientType::query()->where('key', 'person')->value('id');
    $draftStatusId = InvoiceStatus::query()->where('key', 'draft')->value('id');

    $client = Client::query()->create([
        'user_id' => $user->id,
        'client_type_id' => $personTypeId,
        'display_name' => 'KPO End Boundary Client',
        'is_active' => true,
    ]);

    $invoice = Invoice::query()->create([
        'client_id' => $client->id,
        'status_id' => $draftStatusId,
        'invoice_year' => 2026,
        'invoice_seq' => 4,
        'invoice_number' => '004/2026',
        'issue_date' => '2026-04-01',
        'issue_date_to' => '2026-04-30',
        'subtotal' => 7000,
        'total' => 7000,
    ]);
    $invoice->forceFill(['created_at' => '2026-04-30 23:59:59', 'updated_at' => '2026-04-30 23:59:59'])->saveQuietly();

    $report = app(GenerateMonthlyKpoReportAction::class)->execute(
        GenerateMonthlyKpoReportData::fromArray([
            'user_id' => $user->id,
            'year' => 2026,
            'month' => 4,
        ])
    );

    expect($report->rows)->toHaveCount(1);
});

test('kpo report excludes invoice created at midnight of next month', function () {
    $user = User::factory()->create();
    $personTypeId = ClientType::query()->where('key', 'person')->value('id');
    $draftStatusId = InvoiceStatus::query()->where('key', 'draft')->value('id');

    $client = Client::query()->create([
        'user_id' => $user->id,
        'client_type_id' => $personTypeId,
        'display_name' => 'KPO Next Month Client',
        'is_active' => true,
    ]);

    // Kreirana tačno na početku sledećeg meseca — ne sme ući u aprila KPO
    $invoice = Invoice::query()->create([
        'client_id' => $client->id,
        'status_id' => $draftStatusId,
        'invoice_year' => 2026,
        'invoice_seq' => 5,
        'invoice_number' => '005/2026',
        'issue_date' => '2026-04-01',
        'issue_date_to' => '2026-04-30',
        'subtotal' => 3000,
        'total' => 3000,
    ]);
    $invoice->forceFill(['created_at' => '2026-05-01 00:00:00', 'updated_at' => '2026-05-01 00:00:00'])->saveQuietly();

    $aprilReport = app(GenerateMonthlyKpoReportAction::class)->execute(
        GenerateMonthlyKpoReportData::fromArray([
            'user_id' => $user->id,
            'year' => 2026,
            'month' => 4,
        ])
    );

    expect($aprilReport->rows)->toHaveCount(0);
});
