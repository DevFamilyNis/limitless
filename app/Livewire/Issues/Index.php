<?php

namespace App\Livewire\Issues;

use App\Models\Issue;
use App\Models\IssueCategory;
use App\Models\IssuePriority;
use App\Models\Project;
use App\Models\User;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Livewire\WithPagination;

class Index extends Component
{
    use WithPagination;

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

    public function updated(string $name): void
    {
        if (in_array($name, ['projectId', 'search', 'categoryId', 'priorityId', 'clientId', 'assigneeId'], true)) {
            $this->resetPage();
        }
    }

    public function render(): View
    {
        $issues = Issue::query()
            ->with(['project', 'status', 'priority', 'category', 'client', 'assignee'])
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
            ->paginate(15);

        return view('livewire.issues.index', [
            'issues' => $issues,
            'projects' => Project::query()->where('user_id', Auth::id())->orderBy('name')->get(),
            'categories' => IssueCategory::query()->where('is_active', true)->orderBy('name')->get(),
            'priorities' => IssuePriority::query()->orderBy('sort_order')->get(),
            'clients' => \App\Models\Client::query()->where('user_id', Auth::id())->orderBy('display_name')->get(),
            'assignees' => User::query()->orderBy('name')->get(),
        ])->layout('layouts.app', [
            'title' => 'Issues',
        ]);
    }
}
