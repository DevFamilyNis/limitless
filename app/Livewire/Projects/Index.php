<?php

namespace App\Livewire\Projects;

use App\Models\Project;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Livewire\WithPagination;

class Index extends Component
{
    use WithPagination;

    public string $search = '';

    public string $statusFilter = 'all';

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function updatedStatusFilter(): void
    {
        $this->resetPage();
    }

    public function toggleActive(int $projectId): void
    {
        $project = Project::query()
            ->where('user_id', Auth::id())
            ->findOrFail($projectId);

        $project->update([
            'is_active' => ! $project->is_active,
        ]);

        session()->flash('status', 'Status projekta je uspešno ažuriran.');
    }

    public function deleteProject(int $projectId): void
    {
        $project = Project::query()
            ->where('user_id', Auth::id())
            ->findOrFail($projectId);

        $project->delete();

        session()->flash('status', 'Projekat je uspešno obrisan.');
    }

    public function render(): View
    {
        $projects = Project::query()
            ->where('user_id', Auth::id())
            ->when($this->search !== '', function ($query): void {
                $query->where(function ($innerQuery): void {
                    $innerQuery
                        ->where('name', 'like', '%'.$this->search.'%')
                        ->orWhere('code', 'like', '%'.$this->search.'%')
                        ->orWhere('description', 'like', '%'.$this->search.'%');
                });
            })
            ->when($this->statusFilter === 'active', fn ($query) => $query->where('is_active', true))
            ->when($this->statusFilter === 'inactive', fn ($query) => $query->where('is_active', false))
            ->latest('id')
            ->paginate(10);

        return view('livewire.projects.index', [
            'projects' => $projects,
        ])->layout('layouts.app', [
            'title' => 'Projekti',
        ]);
    }
}
