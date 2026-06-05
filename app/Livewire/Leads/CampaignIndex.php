<?php

declare(strict_types=1);

namespace App\Livewire\Leads;

use App\Models\LeadCampaign;
use App\Models\LeadStatus;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\DB;
use Livewire\Component;

class CampaignIndex extends Component
{
    public function render(): View
    {
        $campaigns = LeadCampaign::query()
            ->orderBy('name')
            ->get();

        $statuses = LeadStatus::query()->orderBy('id')->get();

        $leadCounts = DB::table('leads')
            ->join('lead_statuses', 'lead_statuses.id', '=', 'leads.lead_status_id')
            ->selectRaw('leads.lead_campaign_id, lead_statuses.key as status_key, count(*) as total')
            ->groupBy('leads.lead_campaign_id', 'lead_statuses.key')
            ->get()
            ->groupBy('lead_campaign_id')
            ->map(fn ($rows) => $rows->keyBy('status_key'));

        return view('livewire.leads.campaign-index', [
            'campaigns' => $campaigns,
            'statuses' => $statuses,
            'leadCounts' => $leadCounts,
        ])->layout('layouts.app', [
            'title' => __('messages.lead_campaigns.title'),
        ]);
    }
}
