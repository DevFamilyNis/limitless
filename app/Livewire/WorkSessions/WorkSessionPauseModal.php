<?php

declare(strict_types=1);

namespace App\Livewire\WorkSessions;

use App\Domain\WorkSessions\Actions\ResumeWorkSessionAction;
use App\Domain\WorkSessions\DTO\ResumeWorkSessionData;
use App\Models\WorkSession;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;
use Livewire\Attributes\On;
use Livewire\Component;

class WorkSessionPauseModal extends Component
{
    public bool $show = false;

    public function mount(): void
    {
        $this->syncState();
    }

    #[On('work-session-paused')]
    public function onPaused(): void
    {
        $this->show = true;
    }

    public function resume(): void
    {
        app(ResumeWorkSessionAction::class)->execute(
            ResumeWorkSessionData::fromArray(['user_id' => Auth::id()])
        );

        $this->show = false;
        $this->dispatch('work-session-resumed');
    }

    public function render(): View
    {
        if ($this->show) {
            $this->syncState();
        }

        return view('livewire.work-sessions.work-session-pause-modal');
    }

    private function syncState(): void
    {
        $session = WorkSession::query()
            ->where('user_id', Auth::id())
            ->whereDate('work_date', today())
            ->first();

        $this->show = $session !== null && $session->isPaused();
    }
}
