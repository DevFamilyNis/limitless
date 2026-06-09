<?php

declare(strict_types=1);

namespace App\Livewire\Settings;

use App\Enums\AppSettingKey;
use App\Enums\PermissionKey;
use App\Models\AppSetting;
use Illuminate\Contracts\View\View;
use Livewire\Component;

class WorkSessionSettings extends Component
{
    public int $reminderDelayMinutes = 120;

    public function mount(): void
    {
        $this->reminderDelayMinutes = (int) AppSetting::getValue(
            AppSettingKey::WorkSessionReminderDelayMinutes,
            120
        );
    }

    public function save(): void
    {
        $this->authorize(PermissionKey::ManageSettings->value);

        $this->validate([
            'reminderDelayMinutes' => ['required', 'integer', 'min:15', 'max:480'],
        ]);

        AppSetting::setValue(
            AppSettingKey::WorkSessionReminderDelayMinutes,
            $this->reminderDelayMinutes
        );

        session()->flash('status', 'Podešavanja sačuvana.');
    }

    public function render(): View
    {
        return view('livewire.settings.work-session-settings')
            ->layout('layouts.app', ['title' => 'Radni dan — podešavanja']);
    }
}
