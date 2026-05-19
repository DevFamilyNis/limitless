<?php

use App\Enums\AppSettingKey;
use App\Enums\PermissionKey;
use App\Enums\RoleKey;
use App\Livewire\KpoReports\Index;
use App\Models\AppSetting;
use App\Models\KpoReport;
use App\Models\User;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;

// Pravilo: generateReport() i lockReport() zahtevaju manage-kpo permission.
// view-kpo je odvojen — autentifikovani korisnici mogu čitati KPO stranicu.
// user role ima view-kpo ali NE manage-kpo.
// super-admin bypass-uje sve checks via Gate::before.

beforeEach(function () {
    app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();
    (new \Database\Seeders\RolesAndPermissionsSeeder)->run();
});

// --- CANNOT tests: 403 mora doći iz authorization, ne iz nedostatka podataka ---

test('user without manage-kpo cannot generate kpo report', function () {
    $user = User::factory()->create();
    $user->assignRole(RoleKey::User->value); // user role: view-kpo ✓, manage-kpo ✗

    Livewire::actingAs($user)->test(Index::class)
        ->call('generateReport', 1)
        ->assertForbidden();
});

test('user without manage-kpo cannot lock kpo report', function () {
    $user = User::factory()->create();
    $user->assignRole(RoleKey::User->value);

    $signer = User::factory()->create();
    $report = KpoReport::query()->create([
        'user_id' => $signer->id,
        'year' => 2026,
        'month' => 1,
        'period_from' => '2026-01-01',
        'period_to' => '2026-01-31',
        'products_total' => 0,
        'services_total' => 0,
        'activity_total' => 0,
        'currency' => 'RSD',
    ]);

    Livewire::actingAs($user)->test(Index::class)
        ->call('lockReport', $report->id)
        ->assertForbidden();

    // Potvrda: report nije zaključan — 403 je stigao pre akcije
    expect($report->fresh()?->locked_at)->toBeNull();
});

test('user with no role at all cannot generate kpo report', function () {
    $user = User::factory()->create(); // nema role

    Livewire::actingAs($user)->test(Index::class)
        ->call('generateReport', 1)
        ->assertForbidden();
});

test('user with no role at all cannot lock kpo report', function () {
    $user = User::factory()->create();

    $signer = User::factory()->create();
    $report = KpoReport::query()->create([
        'user_id' => $signer->id,
        'year' => 2026,
        'month' => 2,
        'period_from' => '2026-02-01',
        'period_to' => '2026-02-28',
        'products_total' => 0,
        'services_total' => 0,
        'activity_total' => 0,
        'currency' => 'RSD',
    ]);

    Livewire::actingAs($user)->test(Index::class)
        ->call('lockReport', $report->id)
        ->assertForbidden();

    expect($report->fresh()?->locked_at)->toBeNull();
});

// --- CAN tests: permission prolazi, akcija se izvršava ---

test('user with manage-kpo can generate kpo report', function () {
    Storage::fake('public');
    config()->set('media-library.disk_name', 'public');

    $signer = User::factory()->create();
    AppSetting::setValue(AppSettingKey::OfficialSignerUserId, $signer->id);

    $user = User::factory()->create();
    $user->givePermissionTo(PermissionKey::ManageKpo->value);

    // Nema faktura u ovom mesecu — prazan KPO je validan
    Livewire::actingAs($user)->test(Index::class)
        ->call('generateReport', 3) // mart — prazan period
        ->assertHasNoErrors();

    // Dokaz da je akcija prošla: KPO report je kreiran u bazi
    expect(KpoReport::query()->where('month', 3)->exists())->toBeTrue();
});

test('user with manage-kpo can lock kpo report', function () {
    Storage::fake('public');
    config()->set('media-library.disk_name', 'public');

    $signer = User::factory()->create();
    AppSetting::setValue(AppSettingKey::OfficialSignerUserId, $signer->id);

    $user = User::factory()->create();
    $user->givePermissionTo(PermissionKey::ManageKpo->value);

    $report = KpoReport::query()->create([
        'user_id' => $signer->id,
        'year' => 2026,
        'month' => 4,
        'period_from' => '2026-04-01',
        'period_to' => '2026-04-30',
        'products_total' => 0,
        'services_total' => 0,
        'activity_total' => 0,
        'currency' => 'RSD',
    ]);

    Livewire::actingAs($user)->test(Index::class)
        ->call('lockReport', $report->id)
        ->assertHasNoErrors();

    // Dokaz da je akcija prošla: report je zaključan
    expect($report->fresh()?->locked_at)->not()->toBeNull();
});

test('super-admin can generate kpo report via gate bypass', function () {
    Storage::fake('public');
    config()->set('media-library.disk_name', 'public');

    $signer = User::factory()->create();
    AppSetting::setValue(AppSettingKey::OfficialSignerUserId, $signer->id);

    $superAdmin = User::factory()->create();
    $superAdmin->assignRole(RoleKey::SuperAdmin->value);

    Livewire::actingAs($superAdmin)->test(Index::class)
        ->call('generateReport', 5)
        ->assertHasNoErrors();

    expect(KpoReport::query()->where('month', 5)->exists())->toBeTrue();
});

test('super-admin can lock kpo report via gate bypass', function () {
    Storage::fake('public');
    config()->set('media-library.disk_name', 'public');

    $signer = User::factory()->create();
    AppSetting::setValue(AppSettingKey::OfficialSignerUserId, $signer->id);

    $superAdmin = User::factory()->create();
    $superAdmin->assignRole(RoleKey::SuperAdmin->value);

    $report = KpoReport::query()->create([
        'user_id' => $signer->id,
        'year' => 2026,
        'month' => 6,
        'period_from' => '2026-06-01',
        'period_to' => '2026-06-30',
        'products_total' => 0,
        'services_total' => 0,
        'activity_total' => 0,
        'currency' => 'RSD',
    ]);

    Livewire::actingAs($superAdmin)->test(Index::class)
        ->call('lockReport', $report->id)
        ->assertHasNoErrors();

    expect($report->fresh()?->locked_at)->not()->toBeNull();
});
