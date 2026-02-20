<?php

use App\Mail\MagicLoginLinkMail;
use App\Models\LoginLink;
use App\Models\User;
use Illuminate\Support\Facades\Mail;

test('users can request a magic login link from login screen', function () {
    Mail::fake();

    $user = User::factory()->create();

    $response = $this->from(route('login'))->post(route('magic-login.send'), [
        'magic_email' => $user->email,
    ]);

    $response
        ->assertRedirect(route('login', absolute: false))
        ->assertSessionHas('status', 'Ako email postoji u sistemu, poslali smo link za prijavu.');

    Mail::assertSent(MagicLoginLinkMail::class, fn (MagicLoginLinkMail $mail) => $mail->hasTo($user->email));

    $this->assertDatabaseHas('login_links', [
        'user_id' => $user->id,
    ]);
});

test('requesting a magic link for unknown email does not send mail', function () {
    Mail::fake();

    $response = $this->from(route('login'))->post(route('magic-login.send'), [
        'magic_email' => 'nobody@example.com',
    ]);

    $response
        ->assertRedirect(route('login', absolute: false))
        ->assertSessionHas('status', 'Ako email postoji u sistemu, poslali smo link za prijavu.');

    Mail::assertNothingSent();
    $this->assertDatabaseCount('login_links', 0);
});

test('users can authenticate with valid magic login link', function () {
    Mail::fake();

    $user = User::factory()->create();

    $this->post(route('magic-login.send'), [
        'magic_email' => $user->email,
    ]);

    $mailUrl = null;

    Mail::assertSent(MagicLoginLinkMail::class, function (MagicLoginLinkMail $mail) use (&$mailUrl): bool {
        $mailUrl = $mail->url;

        return true;
    });

    expect($mailUrl)->not->toBeNull();

    $response = $this->get($mailUrl);

    $response->assertRedirect(route('dashboard', absolute: false));
    $this->assertAuthenticatedAs($user);

    $loginLink = LoginLink::query()->where('user_id', $user->id)->first();

    expect($loginLink)->not->toBeNull();
    expect($loginLink?->used_at)->not->toBeNull();
});

test('magic login link can not be reused', function () {
    Mail::fake();

    $user = User::factory()->create();

    $this->post(route('magic-login.send'), [
        'magic_email' => $user->email,
    ]);

    $mailUrl = null;

    Mail::assertSent(MagicLoginLinkMail::class, function (MagicLoginLinkMail $mail) use (&$mailUrl): bool {
        $mailUrl = $mail->url;

        return true;
    });

    expect($mailUrl)->not->toBeNull();

    $this->get($mailUrl);
    $this->post(route('logout'));

    $response = $this->get($mailUrl);

    $response
        ->assertRedirect(route('magic-login.request', absolute: false))
        ->assertSessionHas('status', 'Link je nevažeći ili je istekao. Pošalji novi.');
});
