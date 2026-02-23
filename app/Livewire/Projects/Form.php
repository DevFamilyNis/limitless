<?php

namespace App\Livewire\Projects;

use App\Domain\Projects\Actions\UpsertProjectAction;
use App\Domain\Projects\DTO\UpsertProjectData;
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

        $project = app(UpsertProjectAction::class)->execute(
            UpsertProjectData::fromArray([
                'user_id' => Auth::id(),
                'project_id' => $this->projectId,
                'code' => $validated['code'],
                'name' => $validated['name'],
                'description' => $validated['description'] ?? null,
            ])
        );

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
