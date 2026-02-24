<?php

namespace App\Livewire\Settings\IssueStatuses;

use App\Domain\Settings\Issues\Actions\DeleteIssueStatusAction;
use App\Domain\Settings\Issues\DTO\DeleteIssueStatusData;
use App\Models\IssueStatus;
use Illuminate\Contracts\View\View;
use Livewire\Component;

class Index extends Component
{
    public function deleteStatus(int $statusId): void
    {
        app(DeleteIssueStatusAction::class)->execute(
            DeleteIssueStatusData::fromArray([
                'status_id' => $statusId,
            ])
        );
        session()->flash('status', __('messages.settings.issue_statuses.flash_deleted'));
    }

    public function render(): View
    {
        return view('livewire.settings.issue-statuses.index', [
            'statuses' => IssueStatus::query()->orderBy('sort_order')->get(),
        ])->layout('layouts.app', [
            'title' => __('messages.menu.statuses'),
        ]);
    }
}
