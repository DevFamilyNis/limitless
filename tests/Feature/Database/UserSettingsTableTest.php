<?php

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

test('user settings table has expected structure', function () {
    expect(Schema::hasTable('user_settings'))->toBeTrue();
    expect(Schema::hasColumns('user_settings', [
        'id',
        'user_id',
        'display_name',
        'address',
        'pib',
        'mb',
        'bank_account',
        'default_currency',
        'created_at',
        'updated_at',
    ]))->toBeTrue();
});

test('default user settings row is inserted', function () {
    $userId = DB::table('users')
        ->where('email', 'dev.famil.nis@gmail.com')
        ->value('id');

    expect($userId)->not->toBeNull();

    $this->assertDatabaseHas('user_settings', [
        'user_id' => $userId,
        'display_name' => 'Dev-Family',
        'address' => 'Branka Radicevica 26a',
        'pib' => '113101530',
        'mb' => '66579484',
        'bank_account' => '160-6000001451121-46',
        'default_currency' => 'RSD',
    ]);
});

test('user settings are deleted when user is deleted', function () {
    $userId = DB::table('users')
        ->where('email', 'dev.famil.nis@gmail.com')
        ->value('id');

    expect($userId)->not->toBeNull();

    DB::table('users')->where('id', $userId)->delete();

    $this->assertDatabaseMissing('user_settings', [
        'user_id' => $userId,
    ]);
});
