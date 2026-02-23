<?php

use App\Livewire\Dashboard\DashboardPage;
use App\Models\User;
use Livewire\Livewire;

test('dashboard page is displayed for authenticated user', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->get(route('dashboard'))
        ->assertOk()
        ->assertSee('Prihod ove godine')
        ->assertSee('Neto ovaj mesec')
        ->assertSee('Otvorene fakture');
});

test('dashboard uses fallback paucal thresholds when tax year is missing', function () {
    $user = User::factory()->create();

    Livewire::actingAs($user)
        ->test(DashboardPage::class)
        ->assertSee('6.000.000,00')
        ->assertSee('8.000.000,00');
});
