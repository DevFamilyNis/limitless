<?php

use App\Livewire\Clients\Form;
use App\Livewire\Clients\Index;
use App\Models\Client;
use App\Models\ClientType;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Livewire\Livewire;

test('clients page is displayed', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->get(route('clients.index'))
        ->assertOk();
});

test('create client page is displayed', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->get(route('clients.create'))
        ->assertOk()
        ->assertSee('Novi klijent');
});

test('user can create company client with company details', function () {
    $user = User::factory()->create();
    $companyTypeId = ClientType::query()->where('key', 'company')->value('id');

    $client = Client::query()->create([
        'user_id' => $user->id,
        'client_type_id' => $companyTypeId,
        'display_name' => 'Dev Family DOO',
        'is_active' => true,
    ]);

    Livewire::actingAs($user)->test(Form::class, ['client' => $client])
        ->set('pib', '113101530')
        ->set('mb', '66579484')
        ->set('bankAccount', '160-6000001451121-46')
        ->call('save')
        ->assertRedirect(route('clients.index', absolute: false));

    $clientId = Client::query()
        ->where('user_id', $user->id)
        ->where('display_name', 'Dev Family DOO')
        ->value('id');

    expect($clientId)->not->toBeNull();

    $this->assertDatabaseHas('client_companies', [
        'client_id' => $clientId,
        'pib' => '113101530',
    ]);
});

test('user can search clients', function () {
    $user = User::factory()->create();
    $personTypeId = ClientType::query()->where('key', 'person')->value('id');

    Client::query()->create([
        'user_id' => $user->id,
        'client_type_id' => $personTypeId,
        'display_name' => 'Alfa',
        'is_active' => true,
    ]);

    Client::query()->create([
        'user_id' => $user->id,
        'client_type_id' => $personTypeId,
        'display_name' => 'Beta',
        'is_active' => true,
    ]);

    Livewire::actingAs($user)->test(Index::class)
        ->set('search', 'Alfa')
        ->assertSee('Alfa')
        ->assertDontSee('Beta');
});

test('user can update client details', function () {
    $user = User::factory()->create();
    $personTypeId = ClientType::query()->where('key', 'person')->value('id');

    $client = Client::query()->create([
        'user_id' => $user->id,
        'client_type_id' => $personTypeId,
        'display_name' => 'Stari Naziv',
        'is_active' => true,
    ]);

    Livewire::actingAs($user)->test(Form::class, ['client' => $client])
        ->set('displayName', 'Novi Naziv')
        ->set('phone', '+38160123456')
        ->call('save')
        ->assertRedirect(route('clients.index', absolute: false));

    $this->assertDatabaseHas('clients', [
        'id' => $client->id,
        'display_name' => 'Novi Naziv',
        'phone' => '+38160123456',
    ]);
});

test('user can deactivate and activate client', function () {
    $user = User::factory()->create();
    $personTypeId = ClientType::query()->where('key', 'person')->value('id');

    $client = Client::query()->create([
        'user_id' => $user->id,
        'client_type_id' => $personTypeId,
        'display_name' => 'Test Klijent',
        'is_active' => true,
    ]);

    Livewire::actingAs($user)->test(Index::class)
        ->call('toggleActive', $client->id);

    $this->assertDatabaseHas('clients', [
        'id' => $client->id,
        'is_active' => false,
    ]);

    Livewire::actingAs($user)->test(Index::class)
        ->call('toggleActive', $client->id);

    $this->assertDatabaseHas('clients', [
        'id' => $client->id,
        'is_active' => true,
    ]);
});

test('client can be deleted only when there are no dependent documents', function () {
    $user = User::factory()->create();
    $personTypeId = ClientType::query()->where('key', 'person')->value('id');

    $client = Client::query()->create([
        'user_id' => $user->id,
        'client_type_id' => $personTypeId,
        'display_name' => 'Za Brisanje',
        'is_active' => true,
    ]);

    Schema::create('invoices', function ($table): void {
        $table->id();
        $table->unsignedBigInteger('client_id');
        $table->timestamps();
    });

    DB::table('invoices')->insert([
        'client_id' => $client->id,
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    Livewire::actingAs($user)->test(Index::class)
        ->call('deleteClient', $client->id)
        ->assertSee('Klijent ne može biti obrisan jer ima fakture ili transakcije.');

    $this->assertDatabaseHas('clients', ['id' => $client->id]);

    DB::table('invoices')->where('client_id', $client->id)->delete();

    Livewire::actingAs($user)->test(Index::class)
        ->call('deleteClient', $client->id);

    $this->assertDatabaseMissing('clients', ['id' => $client->id]);
});
