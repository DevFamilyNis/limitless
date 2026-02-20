<?php

namespace App\Livewire\Clients;

use App\Models\Client;
use App\Models\ClientType;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class Form extends Component
{
    public ?int $clientId = null;

    public string $clientTypeId = '';

    public string $displayName = '';

    public string $email = '';

    public string $phone = '';

    public string $address = '';

    public string $note = '';

    public string $pib = '';

    public string $mb = '';

    public string $bankAccount = '';

    public function mount(?Client $client = null): void
    {
        if ($client && $client->user_id !== Auth::id()) {
            abort(404);
        }

        $client = $client?->load('company');

        if ($client) {
            $this->clientId = $client->id;
            $this->clientTypeId = (string) $client->client_type_id;
            $this->displayName = $client->display_name;
            $this->email = (string) $client->email;
            $this->phone = (string) $client->phone;
            $this->address = (string) $client->address;
            $this->note = (string) $client->note;
            $this->pib = (string) optional($client->company)->pib;
            $this->mb = (string) optional($client->company)->mb;
            $this->bankAccount = (string) optional($client->company)->bank_account;

            return;
        }

        $defaultTypeId = ClientType::query()
            ->where('key', 'person')
            ->value('id');

        $this->clientTypeId = (string) $defaultTypeId;
    }

    /**
     * @return array<string, array<int, string>>
     */
    protected function rules(): array
    {
        return [
            'clientTypeId' => ['required', 'exists:client_types,id'],
            'displayName' => ['required', 'string', 'max:255'],
            'email' => ['nullable', 'email:rfc', 'max:255'],
            'phone' => ['nullable', 'string', 'max:255'],
            'address' => ['nullable', 'string', 'max:255'],
            'note' => ['nullable', 'string'],
            'pib' => [$this->isCompanyType() ? 'required' : 'nullable', 'string', 'max:255'],
            'mb' => [$this->isCompanyType() ? 'required' : 'nullable', 'string', 'max:255'],
            'bankAccount' => [$this->isCompanyType() ? 'required' : 'nullable', 'string', 'max:255'],
        ];
    }

    public function save(): void
    {
        $validated = $this->validate();

        $client = $this->clientId
            ? Client::query()
                ->where('user_id', Auth::id())
                ->findOrFail($this->clientId)
            : new Client;

        $client->fill([
            'user_id' => Auth::id(),
            'client_type_id' => (int) $validated['clientTypeId'],
            'display_name' => $validated['displayName'],
            'email' => $validated['email'] ?: null,
            'phone' => $validated['phone'] ?: null,
            'address' => $validated['address'] ?: null,
            'note' => $validated['note'] ?: null,
            'is_active' => $client->exists ? $client->is_active : true,
        ]);

        $client->save();

        if ($this->isCompanyType()) {
            $client->company()->updateOrCreate([], [
                'pib' => $validated['pib'] ?: null,
                'mb' => $validated['mb'] ?: null,
                'bank_account' => $validated['bankAccount'] ?: null,
            ]);
        } else {
            $client->company()->delete();
        }

        $message = $client->wasRecentlyCreated ? 'Klijent je uspešno dodat.' : 'Klijent je uspešno izmenjen.';

        session()->flash('status', $message);
        $this->redirectRoute('clients.index');
    }

    public function isCompanyType(): bool
    {
        if ($this->clientTypeId === '') {
            return false;
        }

        return ClientType::query()
            ->whereKey($this->clientTypeId)
            ->where('key', 'company')
            ->exists();
    }

    public function render(): View
    {
        return view('livewire.clients.form', [
            'clientTypes' => ClientType::query()->orderBy('id')->get(),
            'isEditing' => $this->clientId !== null,
            'isCompany' => $this->isCompanyType(),
        ])->layout('layouts.app', [
            'title' => $this->clientId ? 'Izmena klijenta' : 'Novi klijent',
        ]);
    }
}
