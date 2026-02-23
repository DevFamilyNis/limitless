<?php

namespace App\Livewire\Settings\IssuePriorities;

use App\Models\IssuePriority;
use Illuminate\Contracts\View\View;
use Livewire\Component;

class Index extends Component
{
    public function deletePriority(int $priorityId): void
    {
        IssuePriority::query()->findOrFail($priorityId)->delete();
        session()->flash('status', 'Prioritet je obrisan.');
    }

    public function render(): View
    {
        return view('livewire.settings.issue-priorities.index', [
            'priorities' => IssuePriority::query()->orderBy('sort_order')->get(),
        ])->layout('layouts.app', [
            'title' => 'Issue prioriteti',
        ]);
    }
}
