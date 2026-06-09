<?php

use App\Models\User;
use App\Models\WorkSession;

test('authenticated user without session is redirected to dashboard from protected route', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->get(route('clients.index'))
        ->assertRedirect(route('dashboard'));
});

test('authenticated user with open session can access protected routes', function () {
    $user = User::factory()->create();

    WorkSession::create([
        'user_id' => $user->id,
        'work_date' => today()->toDateString(),
        'started_at' => now()->subHours(2),
    ]);

    $this->actingAs($user)
        ->get(route('clients.index'))
        ->assertOk();
});

test('authenticated user with finished session can still access protected routes', function () {
    $user = User::factory()->create();

    WorkSession::create([
        'user_id' => $user->id,
        'work_date' => today()->toDateString(),
        'started_at' => now()->subHours(8),
        'ended_at' => now()->subHour(),
        'duration_minutes' => 420,
    ]);

    $this->actingAs($user)
        ->get(route('clients.index'))
        ->assertOk();
});

test('dashboard route is accessible even without a session', function () {
    $user = User::factory()->create();

    $this->actingAs($user)
        ->get(route('dashboard'))
        ->assertOk();
});
