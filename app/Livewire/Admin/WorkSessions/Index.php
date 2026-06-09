<?php

declare(strict_types=1);

namespace App\Livewire\Admin\WorkSessions;

use App\Domain\WorkSessions\Actions\ForceDeleteWorkSessionAction;
use App\Domain\WorkSessions\Actions\ForceFinishWorkSessionAction;
use App\Domain\WorkSessions\DTO\ForceDeleteWorkSessionData;
use App\Domain\WorkSessions\DTO\ForceFinishWorkSessionData;
use App\Enums\RoleKey;
use App\Models\User;
use App\Models\WorkSession;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Livewire\WithPagination;

class Index extends Component
{
    use WithPagination;

    public string $selectedUserId = '';

    public string $selectedDate = '';

    public function updatedSelectedUserId(): void
    {
        $this->resetPage();
    }

    public function updatedSelectedDate(): void
    {
        $this->resetPage();
    }

    public function forceFinish(int $id): void
    {
        abort_unless(Auth::user()?->hasRole(RoleKey::SuperAdmin->value), 403);

        app(ForceFinishWorkSessionAction::class)->execute(
            ForceFinishWorkSessionData::fromArray(['work_session_id' => $id])
        );
    }

    public function delete(int $id): void
    {
        abort_unless(Auth::user()?->hasRole(RoleKey::SuperAdmin->value), 403);

        app(ForceDeleteWorkSessionAction::class)->execute(
            ForceDeleteWorkSessionData::fromArray(['work_session_id' => $id])
        );
    }

    public function render(): View
    {
        $sessions = WorkSession::query()
            ->with('user')
            ->when($this->selectedUserId !== '', fn ($q) => $q->where('user_id', (int) $this->selectedUserId))
            ->when($this->selectedDate !== '', fn ($q) => $q->whereDate('work_date', $this->selectedDate))
            ->orderByDesc('work_date')
            ->orderByDesc('started_at')
            ->paginate(20);

        return view('livewire.admin.work-sessions.index', [
            'sessions' => $sessions,
            'users' => User::query()->orderBy('name')->get(),
        ])->layout('layouts.app', ['title' => 'Admin — radni dani']);
    }
}
