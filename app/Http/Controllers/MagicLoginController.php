<?php

namespace App\Http\Controllers;

use App\Models\LoginLink;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;

class MagicLoginController extends Controller
{
    public function __invoke(Request $request, string $token): RedirectResponse
    {
        $hash = hash('sha256', $token);

        $link = LoginLink::query()
            ->where('token_hash', $hash)
            ->first();

        if (! $link || $link->isUsed() || $link->isExpired()) {
            return redirect()->route('magic-login.request')
                ->with('status', 'Link je nevažeći ili je istekao. Pošalji novi.');
        }

        $link->forceFill(['used_at' => now()])->save();

        Auth::login($link->user, remember: true);

        $request->session()->regenerate();

        return redirect()->route('dashboard');
    }
}
