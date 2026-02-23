<?php

use App\Livewire\TaxYears\Form;
use App\Livewire\TaxYears\Index;
use App\Models\TaxYear;
use App\Models\User;
use Livewire\Livewire;

test('tax years page is displayed', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->get(route('tax-years.index'))
        ->assertOk();
});

test('create tax year page is displayed', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->get(route('tax-years.create'))
        ->assertOk()
        ->assertSee('Nova poreska godina');
});

test('current year default thresholds are created for user', function () {
    $user = User::factory()->create();

    Livewire::actingAs($user)->test(Index::class)
        ->assertOk();

    $this->assertDatabaseHas('tax_years', [
        'user_id' => $user->id,
        'year' => now()->year,
        'first_threshold_amount' => '6000000.00',
        'second_threshold_amount' => '8000000.00',
    ]);
});

test('user can create tax year', function () {
    $user = User::factory()->create();

    Livewire::actingAs($user)->test(Form::class)
        ->set('year', '2028')
        ->set('firstThresholdAmount', '7000000.00')
        ->set('secondThresholdAmount', '10000000.00')
        ->call('save')
        ->assertRedirect(route('tax-years.index', absolute: false));

    $this->assertDatabaseHas('tax_years', [
        'user_id' => $user->id,
        'year' => 2028,
        'first_threshold_amount' => '7000000.00',
        'second_threshold_amount' => '10000000.00',
    ]);
});

test('second threshold must be greater than first threshold', function () {
    $user = User::factory()->create();

    Livewire::actingAs($user)->test(Form::class)
        ->set('year', '2027')
        ->set('firstThresholdAmount', '8000000.00')
        ->set('secondThresholdAmount', '7000000.00')
        ->call('save')
        ->assertHasErrors(['secondThresholdAmount' => ['gt']]);
});

test('user can update tax year', function () {
    $user = User::factory()->create();

    $taxYear = TaxYear::query()->create([
        'user_id' => $user->id,
        'year' => 2029,
        'first_threshold_amount' => 7000000,
        'second_threshold_amount' => 10000000,
    ]);

    Livewire::actingAs($user)->test(Form::class, ['taxYear' => $taxYear])
        ->set('firstThresholdAmount', '7500000.00')
        ->set('secondThresholdAmount', '11000000.00')
        ->call('save')
        ->assertRedirect(route('tax-years.index', absolute: false));

    $this->assertDatabaseHas('tax_years', [
        'id' => $taxYear->id,
        'first_threshold_amount' => '7500000.00',
        'second_threshold_amount' => '11000000.00',
    ]);
});

test('user can delete tax year', function () {
    $user = User::factory()->create();

    $taxYear = TaxYear::query()->create([
        'user_id' => $user->id,
        'year' => 2030,
        'first_threshold_amount' => 7000000,
        'second_threshold_amount' => 10000000,
    ]);

    Livewire::actingAs($user)->test(Index::class)
        ->call('deleteTaxYear', $taxYear->id);

    $this->assertDatabaseMissing('tax_years', [
        'id' => $taxYear->id,
    ]);
});
