<?php

declare(strict_types=1);

namespace App\Livewire\WorkSessions;

use App\Domain\WorkSessions\Actions\FinishWorkSessionAction;
use App\Domain\WorkSessions\DTO\FinishWorkSessionData;
use App\Models\WorkSession;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;
use Livewire\Component;

class FinishWorkSessionButton extends Component
{
    // null = no session today, 'open' = running, 'finished' = ended_at set
    public ?string $status = null;

    public function mount(): void
    {
        $session = WorkSession::query()
            ->where('user_id', Auth::id())
            ->whereDate('work_date', today())
            ->first();

        $this->status = match (true) {
            $session === null => null,
            $session->isFinished() => 'finished',
            default => 'open',
        };
    }

    public function finishSession(): void
    {
        $session = WorkSession::query()
            ->where('user_id', Auth::id())
            ->whereDate('work_date', today())
            ->first();

        if ($session === null) {
            return;
        }

        if ($session->isFinished()) {
            $this->status = 'finished';

            return;
        }

        app(FinishWorkSessionAction::class)->execute(
            FinishWorkSessionData::fromArray(['user_id' => Auth::id()])
        );

        $this->status = 'finished';
    }

    public function render(): View
    {
        return view('livewire.work-sessions.finish-work-session-button');
    }
}
