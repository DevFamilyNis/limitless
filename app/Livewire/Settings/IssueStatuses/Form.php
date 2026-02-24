<?php

namespace App\Livewire\Settings\IssueStatuses;

use App\Domain\Settings\Issues\Actions\UpsertIssueStatusAction;
use App\Domain\Settings\Issues\DTO\UpsertIssueStatusData;
use App\Models\IssueStatus;
use Illuminate\Contracts\View\View;
use Illuminate\Validation\Rule;
use Livewire\Component;

class Form extends Component
{
    public ?int $statusId = null;

    public string $key = '';

    public string $name = '';

    public string $sortOrder = '0';

    public bool $isActive = true;

    public function mount(?IssueStatus $issueStatus = null): void
    {
        if ($issueStatus?->exists) {
            $this->statusId = $issueStatus->id;
            $this->key = $issueStatus->key;
            $this->name = $issueStatus->name;
            $this->sortOrder = (string) $issueStatus->sort_order;
            $this->isActive = $issueStatus->is_active;
        }
    }

    protected function rules(): array
    {
        return [
            'key' => ['required', 'string', 'max:255', Rule::unique('issue_statuses', 'key')->ignore($this->statusId)],
            'name' => ['required', 'string', 'max:255'],
            'sortOrder' => ['required', 'integer', 'min:0'],
            'isActive' => ['required', 'boolean'],
        ];
    }

    public function save(): void
    {
        $validated = $this->validate();

        app(UpsertIssueStatusAction::class)->execute(
            UpsertIssueStatusData::fromArray([
                'status_id' => $this->statusId,
                'key' => $validated['key'],
                'name' => $validated['name'],
                'sort_order' => (int) $validated['sortOrder'],
                'is_active' => (bool) $validated['isActive'],
            ])
        );

        $this->redirectRoute('settings.issue-statuses.index');
    }

    public function render(): View
    {
        return view('livewire.settings.issue-statuses.form', [
            'isEditing' => $this->statusId !== null,
        ])->layout('layouts.app', [
            'title' => $this->statusId ? __('messages.settings.issue_statuses.edit_title') : __('messages.settings.issue_statuses.new_title'),
        ]);
    }
}
