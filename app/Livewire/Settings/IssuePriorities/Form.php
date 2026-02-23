<?php

namespace App\Livewire\Settings\IssuePriorities;

use App\Models\IssuePriority;
use Illuminate\Contracts\View\View;
use Illuminate\Validation\Rule;
use Livewire\Component;

class Form extends Component
{
    public ?int $priorityId = null;

    public string $key = '';

    public string $name = '';

    public string $sortOrder = '0';

    public function mount(?IssuePriority $issuePriority = null): void
    {
        if ($issuePriority?->exists) {
            $this->priorityId = $issuePriority->id;
            $this->key = $issuePriority->key;
            $this->name = $issuePriority->name;
            $this->sortOrder = (string) $issuePriority->sort_order;
        }
    }

    protected function rules(): array
    {
        return [
            'key' => ['required', 'string', 'max:255', Rule::unique('issue_priorities', 'key')->ignore($this->priorityId)],
            'name' => ['required', 'string', 'max:255'],
            'sortOrder' => ['required', 'integer', 'min:0'],
        ];
    }

    public function save(): void
    {
        $validated = $this->validate();

        $priority = $this->priorityId ? IssuePriority::query()->findOrFail($this->priorityId) : new IssuePriority;

        $priority->fill([
            'key' => strtolower(trim($validated['key'])),
            'name' => trim($validated['name']),
            'sort_order' => (int) $validated['sortOrder'],
        ]);
        $priority->save();

        $this->redirectRoute('settings.issue-priorities.index');
    }

    public function render(): View
    {
        return view('livewire.settings.issue-priorities.form', [
            'isEditing' => $this->priorityId !== null,
        ])->layout('layouts.app', [
            'title' => $this->priorityId ? 'Izmena prioriteta' : 'Novi prioritet',
        ]);
    }
}
