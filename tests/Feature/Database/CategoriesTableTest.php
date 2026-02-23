<?php

use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

test('categories table has expected structure', function () {
    expect(Schema::hasTable('categories'))->toBeTrue();
    expect(Schema::hasColumns('categories', [
        'id',
        'user_id',
        'category_type_id',
        'name',
        'created_at',
        'updated_at',
    ]))->toBeTrue();
});

test('category foreign keys are enforced', function () {
    $userId = DB::table('users')->insertGetId([
        'name' => 'Category User',
        'email' => 'category.user@example.com',
        'password' => bcrypt('password'),
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    $incomeTypeId = DB::table('category_types')->where('key', 'income')->value('id');

    DB::table('categories')->insert([
        'user_id' => $userId,
        'category_type_id' => $incomeTypeId,
        'name' => 'Prodaja',
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    expect(function (): void {
        DB::table('categories')->insert([
            'user_id' => 999999,
            'category_type_id' => 999999,
            'name' => 'Neispravno',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    })->toThrow(QueryException::class);
});
