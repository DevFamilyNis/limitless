<?php

namespace App\Livewire\Projects;

use App\Models\Project;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Livewire\Component;

class Form extends Component
{
    public ?int $projectId = null;

    public string $code = '';

    public string $name = '';

    public string $description = '';

    public function mount(?Project $project = null): void
    {
        if ($project?->exists && $project->user_id !== Auth::id()) {
            abort(404);
        }

        if (! $project?->exists) {
            return;
        }

        $this->projectId = $project->id;
        $this->code = $project->code;
        $this->name = $project->name;
        $this->description = (string) $project->description;
    }

    protected function rules(): array
    {
        return [
            'code' => [
                'required',
                'string',
                'max:255',
                Rule::unique('projects', 'code')->ignore($this->projectId),
            ],
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
        ];
    }

    public function save(): void
    {
        $validated = $this->validate();

        $project = $this->projectId
            ? Project::query()
                ->where('user_id', Auth::id())
                ->findOrFail($this->projectId)
            : new Project;

        $project->fill([
            'user_id' => Auth::id(),
            'code' => strtoupper(trim($validated['code'])),
            'name' => $validated['name'],
            'description' => $validated['description'] ?: null,
            'is_active' => $project->exists ? $project->is_active : true,
        ]);

        $project->save();

        session()->flash('status', $project->wasRecentlyCreated
            ? 'Projekat je uspešno dodat.'
            : 'Projekat je uspešno izmenjen.');

        $this->redirectRoute('projects.index');
    }

    public function render(): View
    {
        return view('livewire.projects.form', [
            'isEditing' => $this->projectId !== null,
        ])->layout('layouts.app', [
            'title' => $this->projectId ? 'Izmena projekta' : 'Novi projekat',
        ]);
    }
}
