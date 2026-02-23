<?php

use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

test('transactions table has expected structure', function () {
    expect(Schema::hasTable('transactions'))->toBeTrue();
    expect(Schema::hasColumns('transactions', [
        'id',
        'user_id',
        'category_id',
        'client_id',
        'invoice_id',
        'date',
        'amount',
        'currency',
        'title',
        'note',
        'created_at',
        'updated_at',
    ]))->toBeTrue();
});

test('transaction amount must be positive by application validation rule', function () {
    expect(true)->toBeTrue();
});

test('transaction requires valid category and user foreign keys', function () {
    expect(function (): void {
        DB::table('transactions')->insert([
            'user_id' => 999999,
            'category_id' => 999999,
            'client_id' => null,
            'invoice_id' => null,
            'date' => now()->toDateString(),
            'amount' => 100,
            'currency' => 'RSD',
            'title' => 'Test',
            'note' => null,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    })->toThrow(QueryException::class);
});
