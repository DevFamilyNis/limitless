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

class Form extends Component
{
    public ?int $issueId = null;

    public string $projectId = '';

    public string $clientId = '';

    public string $clientContactId = '';

    public string $statusId = '';

    public string $priorityId = '';

    public string $categoryId = '';

    public string $assigneeId = '';

    public string $title = '';

    public string $description = '';

    public string $dueDate = '';

    public function mount(?Issue $issue = null): void
    {
        if ($issue?->exists && $issue->project->user_id !== Auth::id()) {
            abort(404);
        }

        if ($issue?->exists) {
            $this->issueId = $issue->id;
            $this->projectId = (string) $issue->project_id;
            $this->clientId = (string) ($issue->client_id ?? '');
            $this->clientContactId = (string) ($issue->client_contact_id ?? '');
            $this->statusId = (string) $issue->status_id;
            $this->priorityId = (string) $issue->priority_id;
            $this->categoryId = (string) $issue->category_id;
            $this->assigneeId = (string) ($issue->assignee_id ?? '');
            $this->title = $issue->title;
            $this->description = (string) $issue->description;
            $this->dueDate = $issue->due_date?->format('Y-m-d') ?? '';

            return;
        }

        $this->projectId = (string) Project::query()->where('user_id', Auth::id())->value('id');
        $this->statusId = (string) IssueStatus::query()->where('key', 'backlog')->value('id');
        $this->priorityId = (string) IssuePriority::query()->orderBy('sort_order')->value('id');
        $this->categoryId = (string) IssueCategory::query()->where('is_active', true)->orderBy('name')->value('id');
    }

    public function updatedClientId(): void
    {
        if ($this->clientId === '') {
            $this->clientContactId = '';
        }
    }

    protected function rules(): array
    {
        return [
            'projectId' => ['required', 'exists:projects,id'],
            'clientId' => ['nullable', 'exists:clients,id'],
            'clientContactId' => ['nullable', 'exists:client_contacts,id'],
            'statusId' => ['required', 'exists:issue_statuses,id'],
            'priorityId' => ['required', 'exists:issue_priorities,id'],
            'categoryId' => ['required', 'exists:issue_categories,id'],
            'assigneeId' => ['nullable', 'exists:users,id'],
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'dueDate' => ['nullable', 'date'],
        ];
    }

    public function save(): void
    {
        $validated = $this->validate();

        $project = Project::query()
            ->where('user_id', Auth::id())
            ->findOrFail((int) $validated['projectId']);

        $issue = $this->issueId
            ? Issue::query()
                ->whereHas('project', fn ($query) => $query->where('user_id', Auth::id()))
                ->findOrFail($this->issueId)
            : new Issue;

        $status = IssueStatus::query()->findOrFail((int) $validated['statusId']);

        $issue->fill([
            'project_id' => $project->id,
            'client_id' => $validated['clientId'] !== '' ? (int) $validated['clientId'] : null,
            'client_contact_id' => $validated['clientContactId'] !== '' ? (int) $validated['clientContactId'] : null,
            'status_id' => (int) $validated['statusId'],
            'priority_id' => (int) $validated['priorityId'],
            'category_id' => (int) $validated['categoryId'],
            'author_id' => $issue->exists ? $issue->author_id : Auth::id(),
            'assignee_id' => $validated['assigneeId'] !== '' ? (int) $validated['assigneeId'] : null,
            'title' => trim($validated['title']),
            'description' => $validated['description'] ?: null,
            'due_date' => $validated['dueDate'] ?: null,
            'completed_at' => $status->key === 'done' ? now() : null,
        ]);

        $issue->save();

        session()->flash('status', $issue->wasRecentlyCreated
            ? 'Issue je uspešno kreiran.'
            : 'Issue je uspešno izmenjen.');

        $this->redirectRoute('issues.show', ['issue' => $issue->id]);
    }

    public function render(): View
    {
        $clientContacts = collect();
        if ($this->clientId !== '') {
            $clientContacts = \App\Models\ClientContact::query()
                ->where('client_id', (int) $this->clientId)
                ->orderBy('full_name')
                ->get();
        }

        return view('livewire.issues.form', [
            'isEditing' => $this->issueId !== null,
            'projects' => Project::query()->where('user_id', Auth::id())->orderBy('name')->get(),
            'clients' => \App\Models\Client::query()->where('user_id', Auth::id())->where('is_active', true)->orderBy('display_name')->get(),
            'clientContacts' => $clientContacts,
            'statuses' => IssueStatus::query()->where('is_active', true)->orderBy('sort_order')->get(),
            'priorities' => IssuePriority::query()->orderBy('sort_order')->get(),
            'categories' => IssueCategory::query()->where('is_active', true)->orderBy('name')->get(),
            'assignees' => User::query()->orderBy('name')->get(),
        ])->layout('layouts.app', [
            'title' => $this->issueId ? 'Izmena issue-a' : 'Novi issue',
        ]);
    }
}
