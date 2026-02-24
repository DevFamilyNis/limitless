<?php

use App\Models\User;
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

test('user settings row can be inserted', function () {
    $user = User::factory()->create();

    DB::table('user_settings')->insert([
        'user_id' => $user->id,
        'display_name' => 'Dev-Family',
        'address' => 'Branka Radicevica 26a',
        'pib' => '113101530',
        'mb' => '66579484',
        'bank_account' => '160-6000001451121-46',
        'default_currency' => 'RSD',
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    $this->assertDatabaseHas('user_settings', [
        'user_id' => $user->id,
        'display_name' => 'Dev-Family',
    ]);
});

test('user settings are deleted when user is deleted', function () {
    $user = User::factory()->create();

    DB::table('user_settings')->insert([
        'user_id' => $user->id,
        'display_name' => 'Temp User',
        'default_currency' => 'RSD',
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    DB::table('users')->where('id', $user->id)->delete();

    $this->assertDatabaseMissing('user_settings', [
        'user_id' => $user->id,
    ]);
});
