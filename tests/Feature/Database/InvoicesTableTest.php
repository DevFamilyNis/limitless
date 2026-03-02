<?php

use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

test('invoices table has expected structure', function () {
    expect(Schema::hasTable('invoices'))->toBeTrue();
    expect(Schema::hasColumns('invoices', [
        'id',
        'client_id',
        'status_id',
        'invoice_year',
        'invoice_seq',
        'invoice_number',
        'issue_date',
        'issue_date_to',
        'due_date',
        'subtotal',
        'total',
        'note',
        'created_at',
        'updated_at',
    ]))->toBeTrue();
});

test('invoice supporting tables have expected structure', function () {
    expect(Schema::hasTable('invoice_items'))->toBeTrue();
    expect(Schema::hasColumns('invoice_items', [
        'id',
        'invoice_id',
        'project_id',
        'client_project_rate_id',
        'description',
        'quantity',
        'unit_price',
        'amount',
        'created_at',
        'updated_at',
    ]))->toBeTrue();

    expect(Schema::hasTable('invoice_status_changes'))->toBeTrue();
    expect(Schema::hasColumns('invoice_status_changes', [
        'id',
        'invoice_id',
        'from_status_id',
        'to_status_id',
        'changed_at',
        'note',
        'created_at',
        'updated_at',
    ]))->toBeTrue();

    expect(Schema::hasTable('invoice_payments'))->toBeTrue();
    expect(Schema::hasColumns('invoice_payments', [
        'id',
        'invoice_id',
        'payment_method_id',
        'amount',
        'paid_at',
        'note',
        'created_at',
        'updated_at',
    ]))->toBeTrue();
});

test('invoice number and sequence per year must be unique', function () {
    $userId = DB::table('users')->insertGetId([
        'name' => 'Invoice Unique',
        'email' => 'invoice.unique@example.com',
        'password' => bcrypt('password'),
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    $personTypeId = DB::table('client_types')->where('key', 'person')->value('id');
    $draftStatusId = DB::table('invoice_statuses')->where('key', 'draft')->value('id');

    $clientId = DB::table('clients')->insertGetId([
        'user_id' => $userId,
        'client_type_id' => $personTypeId,
        'display_name' => 'Unique Client',
        'is_active' => true,
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    DB::table('invoices')->insert([
        'client_id' => $clientId,
        'status_id' => $draftStatusId,
        'invoice_year' => 2026,
        'invoice_seq' => 1,
        'invoice_number' => '001/2026',
        'issue_date' => now()->toDateString(),
        'issue_date_to' => now()->toDateString(),
        'due_date' => null,
        'subtotal' => 1000,
        'total' => 1000,
        'note' => null,
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    expect(function () use ($clientId, $draftStatusId): void {
        DB::table('invoices')->insert([
            'client_id' => $clientId,
            'status_id' => $draftStatusId,
            'invoice_year' => 2026,
            'invoice_seq' => 1,
            'invoice_number' => '002/2026',
            'issue_date' => now()->toDateString(),
            'issue_date_to' => now()->toDateString(),
            'due_date' => null,
            'subtotal' => 500,
            'total' => 500,
            'note' => null,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    })->toThrow(QueryException::class);

    expect(function () use ($clientId, $draftStatusId): void {
        DB::table('invoices')->insert([
            'client_id' => $clientId,
            'status_id' => $draftStatusId,
            'invoice_year' => 2026,
            'invoice_seq' => 2,
            'invoice_number' => '001/2026',
            'issue_date' => now()->toDateString(),
            'issue_date_to' => now()->toDateString(),
            'due_date' => null,
            'subtotal' => 600,
            'total' => 600,
            'note' => null,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    })->toThrow(QueryException::class);
});
