<?php

declare(strict_types=1);

namespace App\Livewire\WorkSessions;

use App\Domain\WorkSessions\Actions\FinishWorkSessionAction;
use App\Domain\WorkSessions\Actions\StartWorkSessionAction;
use App\Domain\WorkSessions\DTO\FinishWorkSessionData;
use App\Domain\WorkSessions\DTO\StartWorkSessionData;
use App\Domain\WorkSessions\Exceptions\WorkSessionAlreadyStartedException;
use App\Models\WorkSession;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;
use Livewire\Component;

class StartWorkSessionModal extends Component
{
    public bool $show = false;

    // 'start' = no session today, 'resume' = session active on another device
    public string $mode = 'start';

    public function mount(): void
    {
        $this->syncState();
    }

    public function startSession(): void
    {
        try {
            app(StartWorkSessionAction::class)->execute(
                StartWorkSessionData::fromArray(['user_id' => Auth::id()])
            );
        } catch (WorkSessionAlreadyStartedException) {
            // Started on another device between mount and click — close gracefully
        }

        $this->show = false;
        $this->dispatch('work-session-started');
    }

    public function continueSession(): void
    {
        session(['work_session_resumed_'.today()->toDateString() => true]);
        $this->show = false;
    }

    public function finishSession(): void
    {
        app(FinishWorkSessionAction::class)->execute(
            FinishWorkSessionData::fromArray(['user_id' => Auth::id()])
        );

        $this->show = false;
        $this->dispatch('work-session-started');
    }

    public function render(): View
    {
        if ($this->show) {
            $this->syncState();
        }

        return view('livewire.work-sessions.start-work-session-modal');
    }

    private function syncState(): void
    {
        $session = WorkSession::query()
            ->where('user_id', Auth::id())
            ->whereDate('work_date', today())
            ->first();

        if ($session === null) {
            $this->mode = 'start';
            $this->show = true;
        } elseif ($session->isPaused()) {
            // WorkSessionPauseModal handles the paused state
            $this->show = false;
        } elseif (! $session->isFinished()) {
            if (session('work_session_resumed_'.today()->toDateString())) {
                $this->show = false;
            } else {
                $this->mode = 'resume';
                $this->show = true;
            }
        } else {
            $this->show = false;
        }
    }
}
