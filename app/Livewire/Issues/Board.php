<?php

namespace App\Livewire\Issues;

use App\Models\Issue;
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
            ->where('user_id', Auth::id())
            ->orderBy('name')
            ->value('id');
    }

    public function moveIssue(int $issueId, int $toStatusId): void
    {
        $issue = Issue::query()
            ->whereHas('project', fn ($query) => $query->where('user_id', Auth::id()))
            ->findOrFail($issueId);

        $toStatus = IssueStatus::query()->findOrFail($toStatusId);

        $issue->status_id = $toStatus->id;
        $issue->completed_at = $toStatus->key === 'done' ? now() : null;
        $issue->save();

        session()->flash('status', 'Issue je uspešno pomeren.');
    }

    public function render(): View
    {
        $statuses = IssueStatus::query()
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->get();

        $issues = Issue::query()
            ->with(['status', 'priority', 'category', 'client', 'assignee', 'project'])
            ->whereHas('project', fn ($query) => $query->where('user_id', Auth::id()))
            ->when($this->projectId !== '', fn ($query) => $query->where('project_id', (int) $this->projectId))
            ->when($this->search !== '', function ($query): void {
                $query->where(function ($innerQuery): void {
                    $innerQuery
                        ->where('title', 'like', '%'.$this->search.'%')
                        ->orWhere('description', 'like', '%'.$this->search.'%');
                });
            })
            ->when($this->categoryId !== '', fn ($query) => $query->where('category_id', (int) $this->categoryId))
            ->when($this->priorityId !== '', fn ($query) => $query->where('priority_id', (int) $this->priorityId))
            ->when($this->clientId !== '', fn ($query) => $query->where('client_id', (int) $this->clientId))
            ->when($this->assigneeId !== '', fn ($query) => $query->where('assignee_id', (int) $this->assigneeId))
            ->latest('id')
            ->get()
            ->groupBy('status_id');

        return view('livewire.issues.board', [
            'statuses' => $statuses,
            'issuesByStatus' => $issues,
            'projects' => Project::query()->where('user_id', Auth::id())->orderBy('name')->get(),
            'categories' => IssueCategory::query()->where('is_active', true)->orderBy('name')->get(),
            'priorities' => IssuePriority::query()->orderBy('sort_order')->get(),
            'clients' => \App\Models\Client::query()->where('user_id', Auth::id())->orderBy('display_name')->get(),
            'assignees' => User::query()->orderBy('name')->get(),
        ])->layout('layouts.app', [
            'title' => 'Issue board',
        ]);
    }
}
