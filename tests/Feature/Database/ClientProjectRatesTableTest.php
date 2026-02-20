<?php

use App\Models\BillingPeriod;
use App\Models\Client;
use App\Models\ClientType;
use App\Models\Project;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

test('client project rates table has expected structure', function () {
    expect(Schema::hasTable('client_project_rates'))->toBeTrue();
    expect(Schema::hasColumns('client_project_rates', [
        'id',
        'client_id',
        'project_id',
        'billing_period_id',
        'price_amount',
        'currency',
        'is_active',
        'created_at',
        'updated_at',
    ]))->toBeTrue();
});

test('client project rates are deleted when client is deleted', function () {
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

    DB::table('client_project_rates')->insert([
        'client_id' => $client->id,
        'project_id' => $project->id,
        'billing_period_id' => $monthlyId,
        'price_amount' => 20000,
        'currency' => 'RSD',
        'is_active' => true,
        'created_at' => now(),
        'updated_at' => now(),
    ]);

    $client->delete();

    $this->assertDatabaseMissing('client_project_rates', [
        'project_id' => $project->id,
    ]);
});
