<?php

use App\Livewire\ClientProjectRates\Form;
use App\Livewire\ClientProjectRates\Index;
use App\Models\BillingPeriod;
use App\Models\Client;
use App\Models\ClientProjectRate;
use App\Models\ClientType;
use App\Models\Project;
use App\Models\User;
use Livewire\Livewire;

test('client project rates page is displayed', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->get(route('client-project-rates.index'))
        ->assertOk();
});

test('create client project rate page is displayed', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->get(route('client-project-rates.create'))
        ->assertOk()
        ->assertSee('Nova cena klijenta');
});

test('user can create client project rate', function () {
    $user = User::factory()->create();
    $companyTypeId = ClientType::query()->where('key', 'company')->value('id');
    $monthlyId = BillingPeriod::query()->where('key', 'monthly')->value('id');

    $client = Client::query()->create([
        'user_id' => $user->id,
        'client_type_id' => $companyTypeId,
        'display_name' => 'Wolt Partner DOO',
        'is_active' => true,
    ]);

    $project = Project::query()->create([
        'user_id' => $user->id,
        'code' => 'EMPAY',
        'name' => 'EmPay',
        'is_active' => true,
    ]);

    Livewire::actingAs($user)->test(Form::class)
        ->set('clientId', (string) $client->id)
        ->set('projectId', (string) $project->id)
        ->set('billingPeriodId', (string) $monthlyId)
        ->set('priceAmount', '20000')
        ->set('currency', 'RSD')
        ->call('save')
        ->assertRedirect(route('client-project-rates.index', absolute: false));

    $this->assertDatabaseHas('client_project_rates', [
        'client_id' => $client->id,
        'project_id' => $project->id,
        'billing_period_id' => $monthlyId,
        'currency' => 'RSD',
        'is_active' => true,
    ]);
});

test('user can search client project rates', function () {
    $user = User::factory()->create();
    $companyTypeId = ClientType::query()->where('key', 'company')->value('id');
    $monthlyId = BillingPeriod::query()->where('key', 'monthly')->value('id');

    $client = Client::query()->create([
        'user_id' => $user->id,
        'client_type_id' => $companyTypeId,
        'display_name' => 'Wolt Partner DOO',
        'is_active' => true,
    ]);

    $empay = Project::query()->create([
        'user_id' => $user->id,
        'code' => 'EMPAY',
        'name' => 'EmPay',
        'is_active' => true,
    ]);

    $fm = Project::query()->create([
        'user_id' => $user->id,
        'code' => 'FM',
        'name' => 'Facility Management',
        'is_active' => true,
    ]);

    ClientProjectRate::query()->create([
        'client_id' => $client->id,
        'project_id' => $empay->id,
        'billing_period_id' => $monthlyId,
        'price_amount' => 20000,
        'currency' => 'RSD',
        'is_active' => true,
    ]);

    ClientProjectRate::query()->create([
        'client_id' => $client->id,
        'project_id' => $fm->id,
        'billing_period_id' => $monthlyId,
        'price_amount' => 30000,
        'currency' => 'RSD',
        'is_active' => true,
    ]);

    Livewire::actingAs($user)->test(Index::class)
        ->set('search', 'EMPAY')
        ->assertSee('EmPay')
        ->assertDontSee('Facility Management');
});

test('user can update client project rate', function () {
    $user = User::factory()->create();
    $companyTypeId = ClientType::query()->where('key', 'company')->value('id');
    $monthlyId = BillingPeriod::query()->where('key', 'monthly')->value('id');
    $yearlyId = BillingPeriod::query()->where('key', 'yearly')->value('id');

    $client = Client::query()->create([
        'user_id' => $user->id,
        'client_type_id' => $companyTypeId,
        'display_name' => 'Wolt Partner DOO',
        'is_active' => true,
    ]);

    $project = Project::query()->create([
        'user_id' => $user->id,
        'code' => 'EMPAY',
        'name' => 'EmPay',
        'is_active' => true,
    ]);

    $rate = ClientProjectRate::query()->create([
        'client_id' => $client->id,
        'project_id' => $project->id,
        'billing_period_id' => $monthlyId,
        'price_amount' => 20000,
        'currency' => 'RSD',
        'is_active' => true,
    ]);

    Livewire::actingAs($user)->test(Form::class, ['clientProjectRate' => $rate])
        ->set('billingPeriodId', (string) $yearlyId)
        ->set('priceAmount', '240000')
        ->call('save')
        ->assertRedirect(route('client-project-rates.index', absolute: false));

    $this->assertDatabaseHas('client_project_rates', [
        'id' => $rate->id,
        'billing_period_id' => $yearlyId,
        'currency' => 'RSD',
    ]);
});

test('user can deactivate and activate client project rate', function () {
    $user = User::factory()->create();
    $companyTypeId = ClientType::query()->where('key', 'company')->value('id');
    $monthlyId = BillingPeriod::query()->where('key', 'monthly')->value('id');

    $client = Client::query()->create([
        'user_id' => $user->id,
        'client_type_id' => $companyTypeId,
        'display_name' => 'Wolt Partner DOO',
        'is_active' => true,
    ]);

    $project = Project::query()->create([
        'user_id' => $user->id,
        'code' => 'EMPAY',
        'name' => 'EmPay',
        'is_active' => true,
    ]);

    $rate = ClientProjectRate::query()->create([
        'client_id' => $client->id,
        'project_id' => $project->id,
        'billing_period_id' => $monthlyId,
        'price_amount' => 20000,
        'currency' => 'RSD',
        'is_active' => true,
    ]);

    Livewire::actingAs($user)->test(Index::class)
        ->call('toggleActive', $rate->id);

    $this->assertDatabaseHas('client_project_rates', [
        'id' => $rate->id,
        'is_active' => false,
    ]);

    Livewire::actingAs($user)->test(Index::class)
        ->call('toggleActive', $rate->id);

    $this->assertDatabaseHas('client_project_rates', [
        'id' => $rate->id,
        'is_active' => true,
    ]);
});

test('user can delete client project rate', function () {
    $user = User::factory()->create();
    $companyTypeId = ClientType::query()->where('key', 'company')->value('id');
    $monthlyId = BillingPeriod::query()->where('key', 'monthly')->value('id');

    $client = Client::query()->create([
        'user_id' => $user->id,
        'client_type_id' => $companyTypeId,
        'display_name' => 'Wolt Partner DOO',
        'is_active' => true,
    ]);

    $project = Project::query()->create([
        'user_id' => $user->id,
        'code' => 'EMPAY',
        'name' => 'EmPay',
        'is_active' => true,
    ]);

    $rate = ClientProjectRate::query()->create([
        'client_id' => $client->id,
        'project_id' => $project->id,
        'billing_period_id' => $monthlyId,
        'price_amount' => 20000,
        'currency' => 'RSD',
        'is_active' => true,
    ]);

    Livewire::actingAs($user)->test(Index::class)
        ->call('deleteRate', $rate->id);

    $this->assertDatabaseMissing('client_project_rates', [
        'id' => $rate->id,
    ]);
});
