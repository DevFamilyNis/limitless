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
use Livewire\WithPagination;

class Index extends Component
{
    use WithPagination;

    public string $viewMode = 'kanban';

    public string $projectId = '';

    public string $search = '';

    public string $categoryId = '';

    public string $priorityId = '';

    public string $clientId = '';

    public string $assigneeId = '';

    public function mount(): void
    {
        $this->projectId = '';
    }

    public function setViewMode(string $mode): void
    {
        if (! in_array($mode, ['kanban', 'table'], true)) {
            return;
        }

        $this->viewMode = $mode;
        $this->resetPage();
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

    public function updated(string $name): void
    {
        if (in_array($name, ['viewMode', 'projectId', 'search', 'categoryId', 'priorityId', 'clientId', 'assigneeId'], true)) {
            $this->resetPage();
        }
    }

    public function render(): View
    {
        $filters = IssueFiltersData::fromArray([
            'user_id' => Auth::id(),
            'project_id' => $this->projectId !== '' ? (int) $this->projectId : null,
            'search' => $this->search,
            'category_id' => $this->categoryId !== '' ? (int) $this->categoryId : null,
            'priority_id' => $this->priorityId !== '' ? (int) $this->priorityId : null,
            'client_id' => $this->clientId !== '' ? (int) $this->clientId : null,
            'assignee_id' => $this->assigneeId !== '' ? (int) $this->assigneeId : null,
        ]);

        $baseQuery = app(IssueFilteredListQuery::class)->execute($filters)
            ->with(['project', 'status', 'priority', 'category', 'client', 'assignee'])
            ->withCount('comments')
            ->orderByRaw('due_date IS NULL')
            ->orderBy('due_date')
            ->orderByDesc('id');

        $issues = $this->viewMode === 'table'
            ? (clone $baseQuery)->paginate(15)
            : null;

        $issuesByStatus = $this->viewMode === 'kanban'
            ? (clone $baseQuery)->get()->groupBy('status_id')
            : collect();

        $statuses = IssueStatus::query()
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->get();

        return view('livewire.issues.index', [
            'issues' => $issues,
            'issuesByStatus' => $issuesByStatus,
            'statuses' => $statuses,
            'projects' => Project::query()->orderBy('name')->get(),
            'categories' => IssueCategory::query()->where('is_active', true)->orderBy('name')->get(),
            'priorities' => IssuePriority::query()->orderBy('sort_order')->get(),
            'clients' => \App\Models\Client::query()->orderBy('display_name')->get(),
            'assignees' => User::query()->orderBy('name')->get(),
        ])->layout('layouts.app', [
            'title' => __('messages.menu.tasks'),
        ]);
    }
}
