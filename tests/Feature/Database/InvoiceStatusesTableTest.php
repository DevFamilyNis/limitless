<?php

use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

test('invoice statuses table has expected structure', function () {
    expect(Schema::hasTable('invoice_statuses'))->toBeTrue();
    expect(Schema::hasColumns('invoice_statuses', ['id', 'key', 'name', 'created_at', 'updated_at']))->toBeTrue();
});

test('invoice statuses table contains expected default statuses', function () {
    $this->assertDatabaseHas('invoice_statuses', ['key' => 'draft', 'name' => 'Nacrt']);
    $this->assertDatabaseHas('invoice_statuses', ['key' => 'sent', 'name' => 'Poslata']);
    $this->assertDatabaseHas('invoice_statuses', ['key' => 'paid', 'name' => 'Plaćena']);
    $this->assertDatabaseHas('invoice_statuses', ['key' => 'canceled', 'name' => 'Otkazana']);

    expect(DB::table('invoice_statuses')->count())->toBe(4);
});

test('invoice status key must be unique', function () {
    expect(function (): void {
        DB::table('invoice_statuses')->insert([
            'key' => 'draft',
            'name' => 'Nacrt 2',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    })->toThrow(QueryException::class);
});
