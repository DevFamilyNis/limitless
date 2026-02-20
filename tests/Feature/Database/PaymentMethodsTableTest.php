<?php

use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

test('payment methods table has expected structure', function () {
    expect(Schema::hasTable('payment_methods'))->toBeTrue();
    expect(Schema::hasColumns('payment_methods', ['id', 'key', 'name', 'created_at', 'updated_at']))->toBeTrue();
});

test('payment methods table contains expected default values', function () {
    $this->assertDatabaseHas('payment_methods', ['key' => 'bank', 'name' => 'Banka']);
    $this->assertDatabaseHas('payment_methods', ['key' => 'cash', 'name' => 'Gotovina']);
    $this->assertDatabaseHas('payment_methods', ['key' => 'card', 'name' => 'Kartica']);

    expect(DB::table('payment_methods')->count())->toBe(3);
});

test('payment method key must be unique', function () {
    expect(function (): void {
        DB::table('payment_methods')->insert([
            'key' => 'bank',
            'name' => 'Banka 2',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    })->toThrow(QueryException::class);
});
