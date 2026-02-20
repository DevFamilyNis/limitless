<?php

namespace App\Livewire\ClientProjectRates;

use App\Models\BillingPeriod;
use App\Models\Client;
use App\Models\ClientProjectRate;
use App\Models\Project;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class Form extends Component
{
    public ?int $rateId = null;

    public string $clientId = '';

    public string $projectId = '';

    public string $billingPeriodId = '';

    public string $priceAmount = '';

    public string $currency = 'RSD';

    public function mount(?ClientProjectRate $clientProjectRate = null): void
    {
        if ($clientProjectRate?->exists && $clientProjectRate->client->user_id !== Auth::id()) {
            abort(404);
        }

        if ($clientProjectRate?->exists) {
            $this->rateId = $clientProjectRate->id;
            $this->clientId = (string) $clientProjectRate->client_id;
            $this->projectId = (string) $clientProjectRate->project_id;
            $this->billingPeriodId = (string) $clientProjectRate->billing_period_id;
            $this->priceAmount = (string) $clientProjectRate->price_amount;
            $this->currency = (string) $clientProjectRate->currency;

            return;
        }

        $this->clientId = (string) Client::query()
            ->where('user_id', Auth::id())
            ->where('is_active', true)
            ->value('id');

        $this->projectId = (string) Project::query()
            ->where('user_id', Auth::id())
            ->where('is_active', true)
            ->value('id');

        $this->billingPeriodId = (string) BillingPeriod::query()
            ->where('key', 'monthly')
            ->value('id');
    }

    /**
     * @return array<string, array<int, string>>
     */
    protected function rules(): array
    {
        return [
            'clientId' => ['required', 'exists:clients,id'],
            'projectId' => ['required', 'exists:projects,id'],
            'billingPeriodId' => ['required', 'exists:billing_periods,id'],
            'priceAmount' => ['required', 'numeric', 'min:0.01'],
            'currency' => ['required', 'string', 'max:10'],
        ];
    }

    public function save(): void
    {
        $validated = $this->validate();

        $client = Client::query()
            ->where('user_id', Auth::id())
            ->findOrFail((int) $validated['clientId']);

        Project::query()
            ->where('user_id', Auth::id())
            ->findOrFail((int) $validated['projectId']);

        $rate = $this->rateId
            ? ClientProjectRate::query()
                ->whereHas('client', fn ($query) => $query->where('user_id', Auth::id()))
                ->findOrFail($this->rateId)
            : new ClientProjectRate;

        $rate->fill([
            'client_id' => $client->id,
            'project_id' => (int) $validated['projectId'],
            'billing_period_id' => (int) $validated['billingPeriodId'],
            'price_amount' => $validated['priceAmount'],
            'currency' => strtoupper(trim($validated['currency'])),
            'is_active' => $rate->exists ? $rate->is_active : true,
        ]);

        $rate->save();

        session()->flash('status', $rate->wasRecentlyCreated
            ? 'Cena klijenta je uspešno dodata.'
            : 'Cena klijenta je uspešno izmenjena.');

        $this->redirectRoute('client-project-rates.index');
    }

    public function render(): View
    {
        $clients = Client::query()
            ->with(['type', 'person'])
            ->where('user_id', Auth::id())
            ->where(function ($query): void {
                $query->where('is_active', true);

                if ($this->clientId !== '') {
                    $query->orWhere('id', (int) $this->clientId);
                }
            })
            ->orderBy('display_name')
            ->get();

        $projects = Project::query()
            ->where('user_id', Auth::id())
            ->where(function ($query): void {
                $query->where('is_active', true);

                if ($this->projectId !== '') {
                    $query->orWhere('id', (int) $this->projectId);
                }
            })
            ->orderBy('name')
            ->get();

        return view('livewire.client-project-rates.form', [
            'isEditing' => $this->rateId !== null,
            'clients' => $clients,
            'projects' => $projects,
            'billingPeriods' => BillingPeriod::query()->orderBy('id')->get(),
            'hasRequiredData' => $clients->isNotEmpty() && $projects->isNotEmpty(),
        ])->layout('layouts.app', [
            'title' => $this->rateId ? 'Izmena cene klijenta' : 'Nova cena klijenta',
        ]);
    }
}
