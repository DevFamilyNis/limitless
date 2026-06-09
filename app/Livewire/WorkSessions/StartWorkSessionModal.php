<?php

declare(strict_types=1);

namespace App\Livewire\WorkSessions;

use App\Domain\WorkSessions\Actions\StartWorkSessionAction;
use App\Domain\WorkSessions\DTO\StartWorkSessionData;
use App\Models\WorkSession;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;
use Livewire\Component;

class StartWorkSessionModal extends Component
{
    public bool $show = false;

    public function mount(): void
    {
        $this->show = ! WorkSession::query()
            ->where('user_id', Auth::id())
            ->whereDate('work_date', today())
            ->exists();
    }

    public function startSession(): void
    {
        app(StartWorkSessionAction::class)->execute(
            StartWorkSessionData::fromArray(['user_id' => Auth::id()])
        );

        $this->show = false;
        $this->dispatch('work-session-started');
    }

    public function render(): View
    {
        return view('livewire.work-sessions.start-work-session-modal');
    }
}
