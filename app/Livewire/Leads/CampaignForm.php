<?php

declare(strict_types=1);

namespace App\Livewire\Leads;

use App\Domain\LeadCampaigns\Actions\DeleteLeadCampaignAction;
use App\Domain\LeadCampaigns\Actions\UpsertLeadCampaignAction;
use App\Domain\LeadCampaigns\DTO\DeleteLeadCampaignData;
use App\Domain\LeadCampaigns\DTO\UpsertLeadCampaignData;
use App\Domain\LeadCampaigns\Exceptions\LeadCampaignHasLeadsException;
use App\Enums\PermissionKey;
use App\Models\LeadCampaign;
use Illuminate\Contracts\View\View;
use Livewire\Component;

class CampaignForm extends Component
{
    public ?int $campaignId = null;

    public string $name = '';

    public string $description = '';

    public function mount(?LeadCampaign $campaign = null): void
    {
        if ($campaign?->exists) {
            $this->campaignId = $campaign->id;
            $this->name = $campaign->name;
            $this->description = (string) $campaign->description;
        }
    }

    /**
     * @return array<string, array<int, string>>
     */
    protected function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:1000'],
        ];
    }

    public function save(): void
    {
        $this->authorize(PermissionKey::ManageLeads->value);

        $validated = $this->validate();

        app(UpsertLeadCampaignAction::class)->execute(
            UpsertLeadCampaignData::fromArray([
                'campaign_id' => $this->campaignId,
                'name' => $validated['name'],
                'description' => $validated['description'] ?? null,
            ])
        );

        session()->flash(
            'status',
            $this->campaignId === null
                ? __('messages.lead_campaigns.flash_created')
                : __('messages.lead_campaigns.flash_updated')
        );

        $this->redirectRoute('leads.index');
    }

    public function deleteCampaign(): void
    {
        $this->authorize(PermissionKey::ManageLeads->value);

        try {
            app(DeleteLeadCampaignAction::class)->execute(
                DeleteLeadCampaignData::fromArray(['campaign_id' => $this->campaignId])
            );

            session()->flash('status', __('messages.lead_campaigns.flash_deleted'));

            $this->redirectRoute('leads.index');
        } catch (LeadCampaignHasLeadsException) {
            session()->flash('error', __('messages.lead_campaigns.cannot_delete_has_leads'));
        }
    }

    public function render(): View
    {
        return view('livewire.leads.campaign-form', [
            'isEditing' => $this->campaignId !== null,
        ])->layout('layouts.app', [
            'title' => $this->campaignId !== null
                ? __('messages.lead_campaigns.form_edit_title')
                : __('messages.lead_campaigns.form_new_title'),
        ]);
    }
}
