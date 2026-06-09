<?php

namespace Tests;

use App\Models\WorkSession;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    protected function actingAsWithSession(Authenticatable $user, ?string $guard = null): static
    {
        $exists = WorkSession::query()
            ->where('user_id', $user->id)
            ->whereDate('work_date', today())
            ->exists();

        if (! $exists) {
            WorkSession::create([
                'user_id' => $user->id,
                'work_date' => today()->toDateString(),
                'started_at' => now(),
            ]);
        }

        return $this->actingAs($user, $guard ?? 'web');
    }
}
