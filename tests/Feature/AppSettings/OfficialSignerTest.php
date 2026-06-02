<?php

use App\Enums\AppSettingKey;
use App\Livewire\Invoices\Form as InvoiceForm;
use App\Livewire\Invoices\Index as InvoiceIndex;
use App\Models\AppSetting;
use App\Models\Client;
use App\Models\ClientType;
use App\Models\InvoiceStatus;
use App\Models\Project;
use App\Models\User;
use Livewire\Livewire;

// Pravilo: official signer je konfigurisana vrednost, ne auth user.
// Drugi user može kreirati fakturu, ali se na dokumentu koristi podešeni signer.

test('official signer user id is resolved from app settings, not from auth user', function () {
    $signer = User::factory()->create(['name' => 'Official Signer']);
    $creator = User::factory()->create(['name' => 'Regular Creator']);

    AppSetting::setValue(AppSettingKey::OfficialSignerUserId, $signer->id);

    expect(AppSetting::officialSignerUserId())->toBe($signer->id);
    expect(AppSetting::officialSignerUser()?->id)->toBe($signer->id);
});

test('official signer user is different from authenticated user', function () {
    $signer = User::factory()->create(['name' => 'Official Signer']);
    $creator = User::factory()->create(['name' => 'Regular Creator']);

    AppSetting::setValue(AppSettingKey::OfficialSignerUserId, $signer->id);

    expect(AppSetting::officialSignerUserId())->not()->toBe($creator->id);
});

test('different user creates invoice but official signer is used as document owner', function () {
    app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();
    (new \Database\Seeders\RolesAndPermissionsSeeder)->run();

    $signer = User::factory()->create();
    $creator = User::factory()->create();
    $creator->givePermissionTo('manage-invoices');

    AppSetting::setValue(AppSettingKey::OfficialSignerUserId, $signer->id);

    $companyTypeId = ClientType::query()->where('key', 'company')->value('id');
    $draftStatusId = InvoiceStatus::query()->where('key', 'draft')->value('id');

    $client = Client::query()->create([
        'user_id' => $signer->id,
        'client_type_id' => $companyTypeId,
        'display_name' => 'Test Client DOO',
        'is_active' => true,
    ]);

    $project = Project::factory()->create(['user_id' => $signer->id]);

    Livewire::actingAs($creator)->test(InvoiceForm::class)
        ->set('clientId', (string) $client->id)
        ->set('statusId', (string) $draftStatusId)
        ->set('issueDate', now()->subMonthNoOverflow()->startOfMonth()->toDateString())
        ->set('issueDateTo', now()->subMonthNoOverflow()->endOfMonth()->toDateString())
        ->set('dueDate', now()->startOfMonth()->addDays(14)->toDateString())
        ->set('items', [
            [
                'id' => null,
                'projectId' => (string) $project->id,
                'clientProjectRateId' => '',
                'description' => 'Usluga',
                'quantity' => '1.00',
                'unitPrice' => '10000.00',
                'amount' => '10000.00',
            ],
        ])
        ->call('save')
        ->assertRedirect(route('invoices.index', absolute: false));
});

test('invoice system aborts with 503 when official signer is not configured', function () {
    app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();
    (new \Database\Seeders\RolesAndPermissionsSeeder)->run();

    $user = User::factory()->create();
    $user->givePermissionTo('manage-invoices');

    $companyTypeId = ClientType::query()->where('key', 'company')->value('id');
    $draftStatusId = InvoiceStatus::query()->where('key', 'draft')->value('id');

    $client = Client::query()->create([
        'user_id' => $user->id,
        'client_type_id' => $companyTypeId,
        'display_name' => 'Test Client',
        'is_active' => true,
    ]);

    $project = Project::factory()->create(['user_id' => $user->id]);

    Livewire::actingAs($user)->test(InvoiceForm::class)
        ->set('clientId', (string) $client->id)
        ->set('statusId', (string) $draftStatusId)
        ->set('issueDate', now()->subMonthNoOverflow()->startOfMonth()->toDateString())
        ->set('issueDateTo', now()->subMonthNoOverflow()->endOfMonth()->toDateString())
        ->set('items', [
            [
                'id' => null,
                'projectId' => (string) $project->id,
                'clientProjectRateId' => '',
                'description' => 'Usluga',
                'quantity' => '1.00',
                'unitPrice' => '10000.00',
                'amount' => '10000.00',
            ],
        ])
        ->call('save')
        ->assertStatus(503);
});

test('official signer lookup does not depend on name string', function () {
    $signer = User::factory()->create(['name' => 'Some Other Name']);

    AppSetting::setValue(AppSettingKey::OfficialSignerUserId, $signer->id);

    expect(AppSetting::officialSignerUserId())->toBe($signer->id);
    expect(AppSetting::officialSignerUser()?->name)->toBe('Some Other Name');
});

test('invoice system aborts with 503 when official signer user was deleted from database', function () {
    app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();
    (new \Database\Seeders\RolesAndPermissionsSeeder)->run();

    // Scenarijo: ID je u settings-ima, ali korisnik je obrisan.
    // officialSignerUserId() vraća int, ali officialSignerUser() vraća null.
    // resolveOfficialSignerOrFail() mora da uhvati i to i vrati 503.
    $signer = User::factory()->create();
    AppSetting::setValue(AppSettingKey::OfficialSignerUserId, $signer->id);

    $deletedSignerId = $signer->id;
    // Direktno brišemo korisnika — u SQLite test env bez strict FK
    \App\Models\User::query()->where('id', $deletedSignerId)->delete();

    // Potvrđujemo da ID u settings-ima još postoji ali user ne postoji u bazi
    expect(AppSetting::officialSignerUserId())->toBe($deletedSignerId);
    expect(AppSetting::officialSignerUser())->toBeNull();

    $creator = User::factory()->create();
    $creator->givePermissionTo('manage-invoices');
    $companyTypeId = ClientType::query()->where('key', 'company')->value('id');
    $draftStatusId = InvoiceStatus::query()->where('key', 'draft')->value('id');

    $client = Client::query()->create([
        'user_id' => $creator->id,
        'client_type_id' => $companyTypeId,
        'display_name' => 'Deleted Signer Client',
        'is_active' => true,
    ]);

    $project = Project::factory()->create(['user_id' => $creator->id]);

    $invoiceCountBefore = \App\Models\Invoice::query()->count();

    Livewire::actingAs($creator)->test(InvoiceForm::class)
        ->set('clientId', (string) $client->id)
        ->set('statusId', (string) $draftStatusId)
        ->set('issueDate', now()->subMonthNoOverflow()->startOfMonth()->toDateString())
        ->set('issueDateTo', now()->subMonthNoOverflow()->endOfMonth()->toDateString())
        ->set('items', [
            [
                'id' => null,
                'projectId' => (string) $project->id,
                'clientProjectRateId' => '',
                'description' => 'Usluga',
                'quantity' => '1.00',
                'unitPrice' => '5000.00',
                'amount' => '5000.00',
            ],
        ])
        ->call('save')
        ->assertStatus(503);

    // Faktura nije kreirana sa nepostojećim user_id
    expect(\App\Models\Invoice::query()->count())->toBe($invoiceCountBefore);
});

test('mark as paid uses official signer not auth user', function () {
    app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();
    (new \Database\Seeders\RolesAndPermissionsSeeder)->run();

    $signer = User::factory()->create();
    $creator = User::factory()->create();
    $creator->givePermissionTo('manage-invoices');

    AppSetting::setValue(AppSettingKey::OfficialSignerUserId, $signer->id);

    $companyTypeId = ClientType::query()->where('key', 'company')->value('id');
    $draftStatusId = InvoiceStatus::query()->where('key', 'draft')->value('id');
    $paidStatusId = InvoiceStatus::query()->where('key', 'paid')->value('id');
    $year = (int) now()->year;

    $client = Client::query()->create([
        'user_id' => $signer->id,
        'client_type_id' => $companyTypeId,
        'display_name' => 'Mark Paid Client',
        'is_active' => true,
    ]);

    $invoice = \App\Models\Invoice::query()->create([
        'client_id' => $client->id,
        'status_id' => $draftStatusId,
        'invoice_year' => $year,
        'invoice_seq' => 5,
        'invoice_number' => '005/'.$year,
        'issue_date' => now()->toDateString(),
        'issue_date_to' => now()->toDateString(),
        'subtotal' => 5000,
        'total' => 5000,
    ]);

    Livewire::actingAs($creator)->test(InvoiceIndex::class)
        ->call('markAsPaid', $invoice->id);

    $this->assertDatabaseHas('invoices', [
        'id' => $invoice->id,
        'status_id' => $paidStatusId,
    ]);
});
