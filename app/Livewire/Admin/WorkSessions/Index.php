<?php

declare(strict_types=1);

namespace App\Livewire\Admin\WorkSessions;

use App\Domain\WorkSessions\Actions\ForceDeleteWorkSessionAction;
use App\Domain\WorkSessions\Actions\ForceFinishWorkSessionAction;
use App\Domain\WorkSessions\Actions\GenerateWorkSessionReportPdfAction;
use App\Domain\WorkSessions\Actions\UpsertWorkSessionUserSettingAction;
use App\Domain\WorkSessions\DTO\ForceDeleteWorkSessionData;
use App\Domain\WorkSessions\DTO\ForceFinishWorkSessionData;
use App\Domain\WorkSessions\DTO\GenerateWorkSessionReportData;
use App\Domain\WorkSessions\DTO\UpsertWorkSessionUserSettingData;
use App\Enums\AppSettingKey;
use App\Enums\RoleKey;
use App\Models\AppSetting;
use App\Models\User;
use App\Models\WorkSession;
use App\Models\WorkSessionUserSetting;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Livewire\WithPagination;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class Index extends Component
{
    use WithPagination;

    public string $selectedUserId = '';

    public string $selectedDate = '';

    public bool $showFinishConfirm = false;

    public bool $showDeleteConfirm = false;

    public ?int $pendingFinishId = null;

    public ?int $pendingDeleteId = null;

    public string $reportUserId = '';

    public string $reportDateFrom = '';

    public string $reportDateTo = '';

    public bool $showUserSettingsModal = false;

    public ?int $userSettingsUserId = null;

    public string $userSettingsUserName = '';

    public bool $userSettingsReminderEnabled = true;

    public int $userSettingsReminderDelayMinutes = 120;

    public function updatedSelectedUserId(): void
    {
        $this->resetPage();
    }

    public function updatedSelectedDate(): void
    {
        $this->resetPage();
    }

    public function confirmFinish(int $id): void
    {
        abort_unless(Auth::user()?->hasRole(RoleKey::SuperAdmin->value), 403);

        $this->pendingFinishId = $id;
        $this->showFinishConfirm = true;
    }

    public function forceFinish(): void
    {
        abort_unless(Auth::user()?->hasRole(RoleKey::SuperAdmin->value), 403);

        if ($this->pendingFinishId !== null) {
            app(ForceFinishWorkSessionAction::class)->execute(
                ForceFinishWorkSessionData::fromArray(['work_session_id' => $this->pendingFinishId])
            );
        }

        $this->pendingFinishId = null;
        $this->showFinishConfirm = false;
    }

    public function confirmDelete(int $id): void
    {
        abort_unless(Auth::user()?->hasRole(RoleKey::SuperAdmin->value), 403);

        $this->pendingDeleteId = $id;
        $this->showDeleteConfirm = true;
    }

    public function delete(): void
    {
        abort_unless(Auth::user()?->hasRole(RoleKey::SuperAdmin->value), 403);

        if ($this->pendingDeleteId !== null) {
            app(ForceDeleteWorkSessionAction::class)->execute(
                ForceDeleteWorkSessionData::fromArray(['work_session_id' => $this->pendingDeleteId])
            );
        }

        $this->pendingDeleteId = null;
        $this->showDeleteConfirm = false;
    }

    public function openUserSettings(int $userId): void
    {
        abort_unless(Auth::user()?->hasRole(RoleKey::SuperAdmin->value), 403);

        $user = User::query()->findOrFail($userId);
        $userSetting = WorkSessionUserSetting::query()->where('user_id', $userId)->first();

        $this->userSettingsUserId = $userId;
        $this->userSettingsUserName = $user->name;
        $this->userSettingsReminderEnabled = $userSetting?->reminder_enabled
            ?? (bool) AppSetting::getValue(AppSettingKey::WorkSessionReminderEnabled, true);
        $this->userSettingsReminderDelayMinutes = $userSetting?->reminder_delay_minutes
            ?? (int) AppSetting::getValue(AppSettingKey::WorkSessionReminderDelayMinutes, 120);
        $this->showUserSettingsModal = true;
    }

    public function saveUserSettings(): void
    {
        abort_unless(Auth::user()?->hasRole(RoleKey::SuperAdmin->value), 403);

        $this->validate([
            'userSettingsReminderEnabled' => ['required', 'boolean'],
            'userSettingsReminderDelayMinutes' => ['exclude_if:userSettingsReminderEnabled,false', 'required', 'integer', 'min:15', 'max:480'],
        ]);

        app(UpsertWorkSessionUserSettingAction::class)->execute(
            UpsertWorkSessionUserSettingData::fromArray([
                'user_id' => $this->userSettingsUserId,
                'reminder_enabled' => $this->userSettingsReminderEnabled,
                'reminder_delay_minutes' => $this->userSettingsReminderDelayMinutes,
            ])
        );

        $this->userSettingsUserId = null;
        $this->showUserSettingsModal = false;
    }

    public function downloadReport(): BinaryFileResponse
    {
        $this->validate([
            'reportDateFrom' => ['required', 'date'],
            'reportDateTo' => ['required', 'date', 'after_or_equal:reportDateFrom'],
        ]);

        $result = app(GenerateWorkSessionReportPdfAction::class)->execute(
            GenerateWorkSessionReportData::fromArray([
                'user_id' => $this->reportUserId !== '' ? $this->reportUserId : null,
                'date_from' => $this->reportDateFrom,
                'date_to' => $this->reportDateTo,
            ])
        );

        return response()
            ->download($result['path'], $result['filename'])
            ->deleteFileAfterSend(true);
    }

    public function render(): View
    {
        $sessions = WorkSession::query()
            ->with('user')
            ->when($this->selectedUserId !== '', fn ($q) => $q->where('user_id', (int) $this->selectedUserId))
            ->when($this->selectedDate !== '', fn ($q) => $q->whereDate('work_date', $this->selectedDate))
            ->orderByDesc('work_date')
            ->orderByDesc('started_at')
            ->paginate(10);

        return view('livewire.admin.work-sessions.index', [
            'sessions' => $sessions,
            'users' => User::query()->orderBy('name')->get(),
        ])->layout('layouts.app', ['title' => 'Admin — radni dani']);
    }
}
