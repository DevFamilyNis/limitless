<?php

declare(strict_types=1);

namespace App\Livewire\Leads;

use App\Domain\Leads\Actions\AddLeadCommentAction;
use App\Domain\Leads\DTO\AddLeadCommentData;
use App\Enums\PermissionKey;
use App\Models\Lead;
use App\Models\LeadCampaign;
use App\Models\LeadStatus;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class Show extends Component
{
    public LeadCampaign $campaign;

    public Lead $lead;

    public string $commentLeadStatusId = '';

    public string $commentEventType = 'note';

    public string $commentContactMethod = 'phone';

    public string $commentBody = '';

    public string $commentContactedAt = '';

    public string $commentRespondedAt = '';

    public string $commentNextFollowUpAt = '';

    public function mount(LeadCampaign $campaign, Lead $lead): void
    {
        $this->campaign = $campaign;
        $this->lead = $lead->load(['status', 'comments.author', 'comments.status']);
        $this->commentLeadStatusId = (string) $lead->lead_status_id;
    }

    /**
     * @return array<string, array<int, string>>
     */
    protected function rules(): array
    {
        return [
            'commentLeadStatusId' => ['required', 'exists:lead_statuses,id'],
            'commentContactMethod' => ['nullable', 'string', 'max:255'],
            'commentBody' => ['required', 'string', 'min:2'],
            'commentContactedAt' => ['nullable', 'date'],
            'commentRespondedAt' => ['nullable', 'date'],
            'commentNextFollowUpAt' => ['nullable', 'date'],
        ];
    }

    public function addComment(): void
    {
        $this->authorize(PermissionKey::ManageLeads->value);

        $validated = $this->validate();

        app(AddLeadCommentAction::class)->execute(
            AddLeadCommentData::fromArray([
                'user_id' => Auth::id(),
                'lead_id' => $this->lead->id,
                'lead_status_id' => $validated['commentLeadStatusId'],
                'event_type' => $this->commentEventType,
                'contact_method' => $validated['commentContactMethod'] ?? null,
                'outcome' => null,
                'body' => $validated['commentBody'],
                'contacted_at' => $validated['commentContactedAt'] ?? null,
                'responded_at' => $validated['commentRespondedAt'] ?? null,
                'next_follow_up_at' => $validated['commentNextFollowUpAt'] ?? null,
            ])
        );

        $this->reset([
            'commentBody',
            'commentContactedAt',
            'commentRespondedAt',
            'commentNextFollowUpAt',
        ]);

        $this->commentEventType = 'note';
        $this->commentContactMethod = 'phone';
        $this->lead = $this->lead->fresh(['status', 'comments.author', 'comments.status']);
        $this->commentLeadStatusId = (string) $this->lead->lead_status_id;
    }

    public function render(): View
    {
        $this->lead->load(['status', 'comments.author', 'comments.status']);

        return view('livewire.leads.show', [
            'statuses' => LeadStatus::query()->orderBy('id')->get(),
            'campaign' => $this->campaign,
        ])->layout('layouts.app', [
            'title' => __('messages.leads.title').': '.$this->lead->company_name,
        ]);
    }
}
