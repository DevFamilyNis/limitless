<?php

namespace App\Livewire\Projects;

use App\Domain\Projects\Actions\UpsertProjectAction;
use App\Domain\Projects\DTO\UpsertProjectData;
use App\Models\Project;
use App\Support\ProjectColorPalette;
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

    public string $projectColor = '';

    public function mount(?Project $project = null): void
    {

        if (! $project?->exists) {
            return;
        }

        $this->projectId = $project->id;
        $this->code = $project->code;
        $this->name = $project->name;
        $this->description = (string) $project->description;
        $this->projectColor = (string) ($project->project_color ?? '');
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
            'projectColor' => ['nullable', 'string', Rule::in(array_keys(ProjectColorPalette::options()))],
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
                'project_color' => $validated['projectColor'] ?? null,
            ])
        );

        session()->flash('status', $project->wasRecentlyCreated
            ? __('messages.projects.flash_created')
            : __('messages.projects.flash_updated'));

        $this->redirectRoute('projects.index');
    }

    public function render(): View
    {
        return view('livewire.projects.form', [
            'isEditing' => $this->projectId !== null,
            'projectColorOptions' => ProjectColorPalette::selectOptions(),
        ])->layout('layouts.app', [
            'title' => $this->projectId ? __('messages.projects.edit_title') : __('messages.projects.new_title'),
        ]);
    }
}
