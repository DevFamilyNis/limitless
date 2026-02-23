<?php

use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

test('tax years table has expected structure', function () {
    expect(Schema::hasTable('tax_years'))->toBeTrue();
    expect(Schema::hasColumns('tax_years', [
        'id',
        'user_id',
        'year',
        'first_threshold_amount',
        'second_threshold_amount',
        'created_at',
        'updated_at',
    ]))->toBeTrue();
});

test('tax year per user must be unique', function () {
    $userId = DB::table('users')->insertGetId([
        'name' => 'Tax User',
        'email' => 'tax.user@example.com',
        'password' => bcrypt('password'),
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    DB::table('tax_years')->insert([
        'user_id' => $userId,
        'year' => 2026,
        'first_threshold_amount' => 6000000,
        'second_threshold_amount' => 8000000,
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    expect(function () use ($userId): void {
        DB::table('tax_years')->insert([
            'user_id' => $userId,
            'year' => 2026,
            'first_threshold_amount' => 7000000,
            'second_threshold_amount' => 9000000,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    })->toThrow(QueryException::class);
});
