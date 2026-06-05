<?php

declare(strict_types=1);

namespace App\Livewire\Leads;

use App\Domain\Leads\Actions\DeleteLeadAction;
use App\Domain\Leads\DTO\DeleteLeadData;
use App\Domain\Leads\DTO\LeadFiltersData;
use App\Domain\Leads\Queries\LeadListQuery;
use App\Domain\Leads\Queries\LeadStatisticsQuery;
use App\Enums\PermissionKey;
use App\Models\LeadCampaign;
use App\Models\LeadStatus;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Livewire\WithPagination;

class Index extends Component
{
    use WithPagination;

    public LeadCampaign $campaign;

    public string $search = '';

    public string $statusFilter = 'all';

    public function mount(LeadCampaign $campaign): void
    {
        $this->campaign = $campaign;
    }

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
        $this->authorize(PermissionKey::ManageLeads->value);

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
            'campaign_id' => $this->campaign->id,
            'search' => $this->search,
            'status_key' => $this->statusFilter,
        ]);

        return view('livewire.leads.index', [
            'leads' => app(LeadListQuery::class)->execute($filters),
            'statistics' => app(LeadStatisticsQuery::class)->get($this->campaign->id),
            'statuses' => LeadStatus::query()->orderBy('id')->get(),
        ])->layout('layouts.app', [
            'title' => $this->campaign->name,
        ]);
    }
}
