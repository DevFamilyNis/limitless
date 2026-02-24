<?php

namespace App\Livewire\Settings\IssuePriorities;

use App\Domain\Settings\Issues\Actions\DeleteIssuePriorityAction;
use App\Domain\Settings\Issues\DTO\DeleteIssuePriorityData;
use App\Models\IssuePriority;
use Illuminate\Contracts\View\View;
use Livewire\Component;

class Index extends Component
{
    public function deletePriority(int $priorityId): void
    {
        app(DeleteIssuePriorityAction::class)->execute(
            DeleteIssuePriorityData::fromArray([
                'priority_id' => $priorityId,
            ])
        );
        session()->flash('status', __('messages.settings.issue_priorities.flash_deleted'));
    }

    public function render(): View
    {
        return view('livewire.settings.issue-priorities.index', [
            'priorities' => IssuePriority::query()->orderBy('sort_order')->get(),
        ])->layout('layouts.app', [
            'title' => __('messages.menu.priorities'),
        ]);
    }
}
