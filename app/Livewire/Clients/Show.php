<?php

namespace App\Livewire\Clients;

use App\Models\Client;
use Illuminate\Contracts\View\View;
use Livewire\Component;

class Show extends Component
{
    public Client $client;

    public function mount(Client $client): void
    {

        $this->client = $client->load([
            'type',
            'company',
            'person',
            'contacts',
            'projectRates.project',
            'projectRates.billingPeriod',
        ]);

        $this->client->loadCount([
            'invoices',
            'transactions',
            'projectRates',
            'issues',
        ]);
    }

    public function render(): View
    {
        $clientName = $this->client->type?->key === 'person' && $this->client->person
            ? trim($this->client->person->first_name.' '.$this->client->person->last_name)
            : $this->client->display_name;

        return view('livewire.clients.show', [
            'clientName' => $clientName,
        ])->layout('layouts.app', [
            'title' => __('messages.clients.title').': '.$clientName,
        ]);
    }
}
