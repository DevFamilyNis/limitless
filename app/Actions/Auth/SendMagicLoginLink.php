<?php

namespace App\Actions\Auth;

use App\Mail\MagicLoginLinkMail;
use App\Models\LoginLink;
use App\Models\User;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Str;

class SendMagicLoginLink
{
    public function handle(User $user): void
    {
        $plainToken = Str::random(64);
        $expiresAt = now()->addMinutes(10);

        LoginLink::query()->create([
            'user_id' => $user->id,
            'token_hash' => hash('sha256', $plainToken),
            'expires_at' => $expiresAt,
            'ip' => request()->ip(),
            'user_agent' => substr((string) request()->userAgent(), 0, 1024),
        ]);

        $url = URL::temporarySignedRoute(
            'magic-login.consume',
            $expiresAt,
            ['token' => $plainToken],
        );

        Mail::to($user->email)->send(new MagicLoginLinkMail($url));
    }
}
