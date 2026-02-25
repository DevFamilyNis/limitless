<?php

namespace App\Livewire\Projects;

use App\Domain\Projects\Actions\DeleteProjectAction;
use App\Domain\Projects\Actions\ToggleProjectActiveAction;
use App\Domain\Projects\DTO\DeleteProjectData;
use App\Domain\Projects\DTO\ToggleProjectActiveData;
use App\Models\ClientProjectRate;
use App\Models\InvoiceItem;
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
        app(ToggleProjectActiveAction::class)->execute(
            ToggleProjectActiveData::fromArray([
                'user_id' => Auth::id(),
                'project_id' => $projectId,
            ])
        );

        session()->flash('status', __('messages.projects.flash_status_updated'));
    }

    public function deleteProject(int $projectId): void
    {
        app(DeleteProjectAction::class)->execute(
            DeleteProjectData::fromArray([
                'user_id' => Auth::id(),
                'project_id' => $projectId,
            ])
        );

        session()->flash('status', __('messages.projects.flash_deleted'));
    }

    public function render(): View
    {
        $projects = Project::query()
            ->with('user')
            ->addSelect([
                'clients_count' => ClientProjectRate::query()
                    ->selectRaw('COUNT(DISTINCT client_id)')
                    ->whereColumn('project_id', 'projects.id'),
                'current_month_total' => InvoiceItem::query()
                    ->join('invoices', 'invoices.id', '=', 'invoice_items.invoice_id')
                    ->join('clients', 'clients.id', '=', 'invoices.client_id')
                    ->selectRaw('COALESCE(SUM(invoice_items.amount), 0)')
                    ->whereColumn('invoice_items.project_id', 'projects.id')
                    ->whereYear('invoices.issue_date', (int) now()->year)
                    ->whereMonth('invoices.issue_date', (int) now()->month),
            ])
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
            'title' => __('messages.projects.title'),
        ]);
    }
}
