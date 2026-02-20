<?php

namespace App\Http\Controllers\Auth;

use App\Actions\Auth\SendMagicLoginLink;
use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\SendMagicLoginLinkRequest;
use App\Models\User;
use Illuminate\Http\RedirectResponse;

class SendMagicLoginLinkController extends Controller
{
    /**
     * Handle the incoming request.
     */
    public function __invoke(SendMagicLoginLinkRequest $request, SendMagicLoginLink $sendMagicLoginLink): RedirectResponse
    {
        $email = $request->string('magic_email')->toString();

        $user = User::query()
            ->where('email', $email)
            ->first();

        if ($user) {
            $sendMagicLoginLink->handle($user);
        }

        return back()->with('status', 'Ako email postoji u sistemu, poslali smo link za prijavu.');
    }
}
