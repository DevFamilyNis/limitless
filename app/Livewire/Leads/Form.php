<?php

declare(strict_types=1);

namespace App\Livewire\Leads;

use App\Domain\Leads\Actions\UpsertLeadAction;
use App\Domain\Leads\DTO\UpsertLeadData;
use App\Models\Lead;
use App\Models\LeadStatus;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class Form extends Component
{
    public ?int $leadId = null;

    public string $companyName = '';

    public string $email = '';

    public string $phone = '';

    public string $leadStatusId = '';

    public function mount(?Lead $lead = null): void
    {
        if ($lead) {
            $this->leadId = $lead->id;
            $this->companyName = (string) $lead->company_name;
            $this->email = (string) $lead->email;
            $this->phone = (string) $lead->phone;
            $this->leadStatusId = (string) $lead->lead_status_id;

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
        ];
    }

    public function save(): void
    {
        $validated = $this->validate();

        app(UpsertLeadAction::class)->execute(
            UpsertLeadData::fromArray([
                'user_id' => Auth::id(),
                'lead_id' => $this->leadId,
                'lead_status_id' => (int) $validated['leadStatusId'],
                'company_name' => $validated['companyName'],
                'email' => $validated['email'] ?? null,
                'phone' => $validated['phone'] ?? null,
            ])
        );

        session()->flash(
            'status',
            $this->leadId === null ? __('messages.leads.flash_created') : __('messages.leads.flash_updated')
        );

        $this->redirectRoute('leads.index');
    }

    public function render(): View
    {
        return view('livewire.leads.form', [
            'statuses' => LeadStatus::query()->orderBy('id')->get(),
            'isEditing' => $this->leadId !== null,
        ])->layout('layouts.app', [
            'title' => $this->leadId !== null ? __('messages.leads.form_edit_title') : __('messages.leads.form_new_title'),
        ]);
    }
}
