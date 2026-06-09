<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use App\Models\WorkSession;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

final class EnsureWorkSessionStarted
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = Auth::user();

        if ($user === null) {
            return $next($request);
        }

        $hasSession = WorkSession::query()
            ->where('user_id', $user->id)
            ->whereDate('work_date', today())
            ->exists();

        if ($hasSession) {
            return $next($request);
        }

        if ($request->hasHeader('X-Livewire')) {
            return response()->json(['redirect' => route('dashboard')]);
        }

        return redirect()->route('dashboard');
    }
}
