<?php

use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

test('category types table has expected structure', function () {
    expect(Schema::hasTable('category_types'))->toBeTrue();
    expect(Schema::hasColumns('category_types', ['id', 'key', 'name', 'created_at', 'updated_at']))->toBeTrue();
});

test('category types table contains expected default values', function () {
    $this->assertDatabaseHas('category_types', ['key' => 'income', 'name' => 'Prihod']);
    $this->assertDatabaseHas('category_types', ['key' => 'expense', 'name' => 'Trošak']);

    expect(DB::table('category_types')->count())->toBe(2);
});

test('category type key must be unique', function () {
    expect(function (): void {
        DB::table('category_types')->insert([
            'key' => 'income',
            'name' => 'Prihod 2',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    })->toThrow(QueryException::class);
});
