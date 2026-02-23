<?php

namespace App\Livewire\Settings\IssueStatuses;

use App\Models\IssueStatus;
use Illuminate\Contracts\View\View;
use Livewire\Component;

class Index extends Component
{
    public function deleteStatus(int $statusId): void
    {
        IssueStatus::query()->findOrFail($statusId)->delete();
        session()->flash('status', 'Status je obrisan.');
    }

    public function render(): View
    {
        return view('livewire.settings.issue-statuses.index', [
            'statuses' => IssueStatus::query()->orderBy('sort_order')->get(),
        ])->layout('layouts.app', [
            'title' => 'Issue statusi',
        ]);
    }
}
