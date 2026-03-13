<?php

namespace App\Livewire\Issues;

use App\Domain\Issues\Actions\MoveIssueAction;
use App\Domain\Issues\DTO\IssueFiltersData;
use App\Domain\Issues\DTO\MoveIssueData;
use App\Domain\Issues\Queries\IssueFilteredListQuery;
use App\Models\IssueCategory;
use App\Models\IssuePriority;
use App\Models\IssueStatus;
use App\Models\Project;
use App\Models\User;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class Board extends Component
{
    public string $projectId = '';

    public string $search = '';

    public string $categoryId = '';

    public string $priorityId = '';

    public string $clientId = '';

    public string $assigneeId = '';

    public function mount(): void
    {
        $this->projectId = (string) Project::query()
            ->orderBy('name')
            ->value('id');
    }

    public function moveIssue(int $issueId, int $toStatusId): void
    {
        app(MoveIssueAction::class)->execute(
            MoveIssueData::fromArray([
                'user_id' => Auth::id(),
                'issue_id' => $issueId,
                'to_status_id' => $toStatusId,
            ])
        );

        session()->flash('status', __('messages.issues.flash_moved'));
    }

    public function render(): View
    {
        $statuses = IssueStatus::query()
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->get();

        $filters = IssueFiltersData::fromArray([
            'user_id' => Auth::id(),
            'project_id' => $this->projectId !== '' ? (int) $this->projectId : null,
            'search' => $this->search,
            'category_id' => $this->categoryId !== '' ? (int) $this->categoryId : null,
            'priority_id' => $this->priorityId !== '' ? (int) $this->priorityId : null,
            'client_id' => $this->clientId !== '' ? (int) $this->clientId : null,
            'assignee_id' => $this->assigneeId !== '' ? (int) $this->assigneeId : null,
        ]);

        $issues = app(IssueFilteredListQuery::class)->execute($filters)
            ->with(['status', 'priority', 'category', 'client', 'assignee', 'project'])
            ->withCount('comments')
            ->latest('id')
            ->get()
            ->groupBy('status_id');

        return view('livewire.issues.board', [
            'statuses' => $statuses,
            'issuesByStatus' => $issues,
            'projects' => Project::query()->orderBy('name')->get(),
            'categories' => IssueCategory::query()->where('is_active', true)->orderBy('name')->get(),
            'priorities' => IssuePriority::query()->orderBy('sort_order')->get(),
            'clients' => \App\Models\Client::query()->orderBy('display_name')->get(),
            'assignees' => User::query()->orderBy('name')->get(),
        ])->layout('layouts.app', [
            'title' => __('messages.issues.board_title'),
        ]);
    }
}
