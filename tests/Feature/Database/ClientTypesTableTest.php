<?php

use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

test('client types table has expected structure', function () {
    expect(Schema::hasTable('client_types'))->toBeTrue();
    expect(Schema::hasColumns('client_types', ['id', 'key', 'name']))->toBeTrue();
});

test('client types table is seeded with company and person records', function () {
    $this->assertDatabaseHas('client_types', [
        'key' => 'company',
        'name' => 'Firma',
    ]);

    $this->assertDatabaseHas('client_types', [
        'key' => 'person',
        'name' => 'Fizičko lice',
    ]);

    expect(DB::table('client_types')->count())->toBe(2);
});

test('client type key must be unique', function () {
    expect(function (): void {
        DB::table('client_types')->insert([
            'key' => 'company',
            'name' => 'Firma 2',
        ]);
    })->toThrow(QueryException::class);
});
