<?php

use App\Models\Client;
use App\Models\ClientType;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

test('client contacts table has expected structure', function () {
    expect(Schema::hasTable('client_contacts'))->toBeTrue();
    expect(Schema::hasColumns('client_contacts', [
        'id',
        'client_id',
        'full_name',
        'email',
        'phone',
        'position',
        'is_primary',
        'note',
        'created_at',
        'updated_at',
    ]))->toBeTrue();
});

test('client contacts are deleted when client is deleted', function () {
    $user = User::factory()->create();
    $companyTypeId = ClientType::query()->where('key', 'company')->value('id');

    $client = Client::query()->create([
        'user_id' => $user->id,
        'client_type_id' => $companyTypeId,
        'display_name' => 'Wolt Partner DOO',
        'is_active' => true,
    ]);

    DB::table('client_contacts')->insert([
        'client_id' => $client->id,
        'full_name' => 'Nikola Owner',
        'email' => 'nikola@example.com',
        'phone' => '+38160123456',
        'position' => 'Owner',
        'is_primary' => true,
        'note' => null,
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    $client->delete();

    $this->assertDatabaseMissing('client_contacts', [
        'client_id' => $client->id,
    ]);
});
