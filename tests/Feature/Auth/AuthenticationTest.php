<?php

use App\Models\User;

test('login screen can be rendered', function () {
    $response = $this->get(route('login'));

    $response
        ->assertRedirect(route('magic-login.request', absolute: false));
});

test('password login endpoint is disabled', function () {
    $user = User::factory()->create();

    $response = $this->post(route('login.store'), [
        'email' => $user->email,
        'password' => 'password',
    ]);

    $response->assertRedirect(route('magic-login.request', absolute: false));
    $this->assertGuest();
});

test('users can logout', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)->post(route('logout'));

    $response->assertRedirect(route('home'));
    $this->assertGuest();
});
