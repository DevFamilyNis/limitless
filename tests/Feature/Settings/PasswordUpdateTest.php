<?php

use App\Livewire\Settings\Password;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Livewire\Livewire;

test('password can be updated', function () {
    $user = User::factory()->create([
        'password' => Hash::make('password'),
    ]);

    $this->actingAs($user);

    $response = Livewire::test(Password::class)
        ->set('current_password', 'password')
        ->set('password', 'NewPassword123')
        ->set('password_confirmation', 'NewPassword123')
        ->call('updatePassword');

    $response->assertHasNoErrors();

    expect(Hash::check('NewPassword123', $user->refresh()->password))->toBeTrue();
});

test('correct password must be provided to update password', function () {
    $user = User::factory()->create([
        'password' => Hash::make('password'),
    ]);

    $this->actingAs($user);

    $response = Livewire::test(Password::class)
        ->set('current_password', 'wrong-password')
        ->set('password', 'NewPassword123')
        ->set('password_confirmation', 'NewPassword123')
        ->call('updatePassword');

    $response->assertHasErrors(['current_password']);
});
