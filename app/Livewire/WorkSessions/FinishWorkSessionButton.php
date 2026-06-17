<?php

declare(strict_types=1);

namespace App\Livewire\WorkSessions;

use App\Domain\WorkSessions\Actions\FinishWorkSessionAction;
use App\Domain\WorkSessions\Actions\PauseWorkSessionAction;
use App\Domain\WorkSessions\DTO\FinishWorkSessionData;
use App\Domain\WorkSessions\DTO\PauseWorkSessionData;
use App\Models\WorkSession;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;
use Livewire\Attributes\On;
use Livewire\Component;

class FinishWorkSessionButton extends Component
{
    // null = no session today, 'open' = running, 'paused' = paused, 'finished' = ended_at set
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
            $session->isPaused() => 'paused',
            default => 'open',
        };
    }

    public function pauseSession(): void
    {
        $session = WorkSession::query()
            ->where('user_id', Auth::id())
            ->whereDate('work_date', today())
            ->first();

        if ($session === null || $session->isFinished() || $session->isPaused()) {
            return;
        }

        app(PauseWorkSessionAction::class)->execute(
            PauseWorkSessionData::fromArray(['user_id' => Auth::id()])
        );

        $this->status = 'paused';
        $this->dispatch('work-session-paused');
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

    #[On('work-session-resumed')]
    public function onResumed(): void
    {
        $this->status = 'open';
    }

    public function render(): View
    {
        return view('livewire.work-sessions.finish-work-session-button');
    }
}
