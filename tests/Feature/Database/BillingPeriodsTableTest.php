<?php

use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

test('billing periods table has expected structure', function () {
    expect(Schema::hasTable('billing_periods'))->toBeTrue();
    expect(Schema::hasColumns('billing_periods', ['id', 'key', 'name', 'created_at', 'updated_at']))->toBeTrue();
});

test('billing periods table contains expected default values', function () {
    $this->assertDatabaseHas('billing_periods', ['key' => 'monthly', 'name' => 'Mesečno']);
    $this->assertDatabaseHas('billing_periods', ['key' => 'yearly', 'name' => 'Godišnje']);
    $this->assertDatabaseHas('billing_periods', ['key' => 'one_time', 'name' => 'Jednokratno']);

    expect(DB::table('billing_periods')->count())->toBe(3);
});

test('billing period key must be unique', function () {
    expect(function (): void {
        DB::table('billing_periods')->insert([
            'key' => 'monthly',
            'name' => 'Mesečno 2',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    })->toThrow(QueryException::class);
});
