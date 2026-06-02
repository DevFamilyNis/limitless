<?php

declare(strict_types=1);

use App\Enums\PermissionKey;
use App\Enums\RoleKey;
use App\Livewire\TaxYears\Form as TaxYearForm;
use App\Livewire\TaxYears\Index as TaxYearIndex;
use App\Models\TaxYear;
use App\Models\User;
use Illuminate\Auth\Access\AuthorizationException;

// Tax year views use Flux UI. Direct component instantiation avoids view rendering
// while still exercising the full authorization + domain logic path.

beforeEach(function () {
    app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();
    (new \Database\Seeders\RolesAndPermissionsSeeder)->run();
});

// ─── CANNOT: user without manage-tax-years ───────────────────────────────────

test('user without manage-tax-years cannot save tax year through form component', function () {
    $user = User::factory()->create();
    $this->actingAs($user);

    $initialCount = TaxYear::query()->count();

    expect(fn () => (new TaxYearForm)->save())
        ->toThrow(AuthorizationException::class);

    expect(TaxYear::query()->count())->toBe($initialCount);
});

test('user without manage-tax-years cannot delete tax year', function () {
    $user = User::factory()->create();
    $this->actingAs($user);

    $taxYear = TaxYear::query()->create([
        'user_id' => $user->id,
        'year' => 2099,
        'first_threshold_amount' => 6000000,
        'second_threshold_amount' => 8000000,
    ]);

    expect(fn () => (new TaxYearIndex)->deleteTaxYear($taxYear->id))
        ->toThrow(AuthorizationException::class);

    expect(TaxYear::find($taxYear->id))->not()->toBeNull();
});

// ─── CAN: user with manage-tax-years ─────────────────────────────────────────

test('user with manage-tax-years can save tax year', function () {
    $user = User::factory()->create();
    $user->givePermissionTo(PermissionKey::ManageTaxYears->value);
    $this->actingAs($user);

    $component = new TaxYearForm;
    $component->year = '2096';
    $component->firstThresholdAmount = '6000000.00';
    $component->secondThresholdAmount = '8000000.00';

    try {
        $component->save();
    } catch (\Throwable) {
        // redirectRoute may throw outside the Livewire lifecycle — tax year is already saved
    }

    expect(TaxYear::query()->where('year', 2096)->exists())->toBeTrue();
});

test('user with manage-tax-years can delete tax year', function () {
    $user = User::factory()->create();
    $user->givePermissionTo(PermissionKey::ManageTaxYears->value);
    $this->actingAs($user);

    $taxYear = TaxYear::query()->create([
        'user_id' => $user->id,
        'year' => 2098,
        'first_threshold_amount' => 6000000,
        'second_threshold_amount' => 8000000,
    ]);

    (new TaxYearIndex)->deleteTaxYear($taxYear->id);

    expect(TaxYear::find($taxYear->id))->toBeNull();
});

// ─── SUPER-ADMIN: Gate::before bypass ────────────────────────────────────────

test('super-admin can delete tax year via gate bypass', function () {
    $superAdmin = User::factory()->create();
    $superAdmin->assignRole(RoleKey::SuperAdmin->value);
    $this->actingAs($superAdmin);

    $taxYear = TaxYear::query()->create([
        'user_id' => $superAdmin->id,
        'year' => 2097,
        'first_threshold_amount' => 6000000,
        'second_threshold_amount' => 8000000,
    ]);

    (new TaxYearIndex)->deleteTaxYear($taxYear->id);

    expect(TaxYear::find($taxYear->id))->toBeNull();
});
