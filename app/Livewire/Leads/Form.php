<?php

declare(strict_types=1);

namespace App\Livewire\Leads;

use App\Domain\Leads\Actions\AddLeadCommentAction;
use App\Domain\Leads\Actions\UpsertLeadAction;
use App\Domain\Leads\DTO\AddLeadCommentData;
use App\Domain\Leads\DTO\UpsertLeadData;
use App\Enums\PermissionKey;
use App\Models\Lead;
use App\Models\LeadCampaign;
use App\Models\LeadStatus;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class Form extends Component
{
    public ?int $leadId = null;

    public LeadCampaign $campaign;

    public string $companyName = '';

    public string $email = '';

    public string $phone = '';

    public string $leadStatusId = '';

    public string $leadCampaignId = '';

    public string $commentBody = '';

    public string $commentContactMethod = 'phone';

    public string $commentContactedAt = '';

    public string $commentRespondedAt = '';

    public string $commentNextFollowUpAt = '';

    public function mount(LeadCampaign $campaign, ?Lead $lead = null): void
    {
        $this->campaign = $campaign;
        $this->leadCampaignId = (string) $campaign->id;

        if ($lead) {
            $this->leadId = $lead->id;
            $this->companyName = (string) $lead->company_name;
            $this->email = (string) $lead->email;
            $this->phone = (string) $lead->phone;
            $this->leadStatusId = (string) $lead->lead_status_id;
            $this->leadCampaignId = (string) $lead->lead_campaign_id;

            return;
        }

        $this->leadStatusId = (string) LeadStatus::query()->where('key', 'new')->value('id');
    }

    /**
     * @return array<string, array<int, string>>
     */
    protected function rules(): array
    {
        return [
            'companyName' => ['required', 'string', 'max:255'],
            'email' => ['nullable', 'email:rfc', 'max:255'],
            'phone' => ['nullable', 'string', 'max:255'],
            'leadStatusId' => ['required', 'exists:lead_statuses,id'],
            'leadCampaignId' => ['required', 'exists:lead_campaigns,id'],
            'commentBody' => ['nullable', 'string', 'min:2'],
            'commentContactMethod' => ['nullable', 'string', 'max:255'],
            'commentContactedAt' => ['nullable', 'date'],
            'commentRespondedAt' => ['nullable', 'date'],
            'commentNextFollowUpAt' => ['nullable', 'date'],
        ];
    }

    public function save(): void
    {
        $this->authorize(PermissionKey::ManageLeads->value);

        $validated = $this->validate();

        $lead = app(UpsertLeadAction::class)->execute(
            UpsertLeadData::fromArray([
                'user_id' => Auth::id(),
                'lead_id' => $this->leadId,
                'lead_campaign_id' => (int) $validated['leadCampaignId'],
                'lead_status_id' => (int) $validated['leadStatusId'],
                'company_name' => $validated['companyName'],
                'email' => $validated['email'] ?? null,
                'phone' => $validated['phone'] ?? null,
            ])
        );

        if ($this->leadId === null && trim($validated['commentBody'] ?? '') !== '') {
            app(AddLeadCommentAction::class)->execute(
                AddLeadCommentData::fromArray([
                    'user_id' => Auth::id(),
                    'lead_id' => $lead->id,
                    'lead_status_id' => (int) $validated['leadStatusId'],
                    'event_type' => 'note',
                    'contact_method' => $validated['commentContactMethod'] ?? null,
                    'outcome' => null,
                    'body' => $validated['commentBody'],
                    'contacted_at' => $validated['commentContactedAt'] ?? null,
                    'responded_at' => $validated['commentRespondedAt'] ?? null,
                    'next_follow_up_at' => $validated['commentNextFollowUpAt'] ?? null,
                ])
            );
        }

        session()->flash(
            'status',
            $this->leadId === null ? __('messages.leads.flash_created') : __('messages.leads.flash_updated')
        );

        $this->redirectRoute('leads.campaign', $this->campaign);
    }

    public function render(): View
    {
        return view('livewire.leads.form', [
            'statuses' => LeadStatus::query()->orderBy('id')->get(),
            'isEditing' => $this->leadId !== null,
            'campaigns' => LeadCampaign::query()->orderBy('name')->get(),
        ])->layout('layouts.app', [
            'title' => $this->leadId !== null ? __('messages.leads.form_edit_title') : __('messages.leads.form_new_title'),
        ]);
    }
}
