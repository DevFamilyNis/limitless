<?php

declare(strict_types=1);

namespace App\Livewire\Settings;

use App\Enums\AppSettingKey;
use App\Enums\RoleKey;
use App\Models\AppSetting;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class WorkSessionSettings extends Component
{
    public bool $reminderEnabled = true;

    public int $reminderDelayMinutes = 120;

    public function mount(): void
    {
        abort_unless(Auth::user()?->hasRole(RoleKey::SuperAdmin->value), 403);

        $this->reminderEnabled = (bool) AppSetting::getValue(
            AppSettingKey::WorkSessionReminderEnabled,
            true
        );

        $this->reminderDelayMinutes = (int) AppSetting::getValue(
            AppSettingKey::WorkSessionReminderDelayMinutes,
            120
        );
    }

    public function save(): void
    {
        abort_unless(Auth::user()?->hasRole(RoleKey::SuperAdmin->value), 403);

        $this->validate([
            'reminderEnabled' => ['required', 'boolean'],
            'reminderDelayMinutes' => ['exclude_if:reminderEnabled,false', 'required', 'integer', 'min:15', 'max:480'],
        ]);

        AppSetting::setValue(AppSettingKey::WorkSessionReminderEnabled, $this->reminderEnabled);

        if ($this->reminderEnabled) {
            AppSetting::setValue(
                AppSettingKey::WorkSessionReminderDelayMinutes,
                $this->reminderDelayMinutes
            );
        }

        session()->flash('status', 'Podešavanja sačuvana.');
    }

    public function render(): View
    {
        return view('livewire.settings.work-session-settings')
            ->layout('layouts.app', ['title' => 'Radni dan — podešavanja']);
    }
}
