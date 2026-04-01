<?php

declare(strict_types=1);

namespace App\Livewire\Leads;

use App\Domain\Leads\Actions\DeleteLeadAction;
use App\Domain\Leads\DTO\DeleteLeadData;
use App\Domain\Leads\DTO\LeadFiltersData;
use App\Domain\Leads\Queries\LeadListQuery;
use App\Domain\Leads\Queries\LeadStatisticsQuery;
use App\Models\LeadStatus;
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

    public function deleteLead(int $leadId): void
    {
        app(DeleteLeadAction::class)->execute(
            DeleteLeadData::fromArray([
                'user_id' => Auth::id(),
                'lead_id' => $leadId,
            ])
        );

        session()->flash('status', __('messages.leads.flash_deleted'));
    }

    public function render(): View
    {
        $filters = LeadFiltersData::fromArray([
            'search' => $this->search,
            'status_key' => $this->statusFilter,
        ]);

        return view('livewire.leads.index', [
            'leads' => app(LeadListQuery::class)->execute($filters),
            'statistics' => app(LeadStatisticsQuery::class)->get(),
            'statuses' => LeadStatus::query()->orderBy('id')->get(),
        ])->layout('layouts.app', [
            'title' => __('messages.leads.title'),
        ]);
    }
}
