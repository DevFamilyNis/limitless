<?php

declare(strict_types=1);

namespace App\Livewire\WorkSessions;

use App\Domain\WorkSessions\Actions\AcknowledgeWorkSessionReminderAction;
use App\Domain\WorkSessions\DTO\AcknowledgeWorkSessionReminderData;
use App\Models\WorkSession;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;
use Livewire\Component;

class WorkSessionReminderModal extends Component
{
    public bool $show = false;

    public function mount(): void
    {
        $session = WorkSession::query()
            ->where('user_id', Auth::id())
            ->whereDate('work_date', today())
            ->first();

        $this->show = $session !== null
            && $session->ended_at === null
            && $session->reminder_due_at !== null
            && $session->reminder_due_at->isPast()
            && $session->reminder_acknowledged_at === null;
    }

    public function acknowledge(): void
    {
        app(AcknowledgeWorkSessionReminderAction::class)->execute(
            AcknowledgeWorkSessionReminderData::fromArray(['user_id' => Auth::id()])
        );

        $this->show = false;
    }

    public function render(): View
    {
        return view('livewire.work-sessions.work-session-reminder-modal');
    }
}
