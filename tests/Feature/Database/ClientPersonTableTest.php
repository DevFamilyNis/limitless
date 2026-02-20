<?php

use App\Models\Client;
use App\Models\ClientType;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

test('client person table has expected structure', function () {
    expect(Schema::hasTable('client_person'))->toBeTrue();
    expect(Schema::hasColumns('client_person', [
        'client_id',
        'first_name',
        'last_name',
        'created_at',
        'updated_at',
    ]))->toBeTrue();
});

test('client person is deleted when client is deleted', function () {
    $user = User::factory()->create();
    $personTypeId = ClientType::query()->where('key', 'person')->value('id');

    $client = Client::query()->create([
        'user_id' => $user->id,
        'client_type_id' => $personTypeId,
        'display_name' => 'Pera Peric',
        'is_active' => true,
    ]);

    DB::table('client_person')->insert([
        'client_id' => $client->id,
        'first_name' => 'Pera',
        'last_name' => 'Peric',
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    $client->delete();

    $this->assertDatabaseMissing('client_person', [
        'client_id' => $client->id,
    ]);
});
