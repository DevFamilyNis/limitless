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

    public string $firstName = '';

    public string $lastName = '';

    /**
     * @var array<int, array{id:int|null,full_name:string,email:string,phone:string,position:string,is_primary:bool,note:string}>
     */
    public array $contacts = [];

    public function mount(?Client $client = null): void
    {
        if ($client && $client->user_id !== Auth::id()) {
            abort(404);
        }

        $client = $client?->load(['company', 'person', 'contacts']);

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
            $this->firstName = (string) optional($client->person)->first_name;
            $this->lastName = (string) optional($client->person)->last_name;
            $this->contacts = $client->contacts
                ->map(fn ($contact): array => [
                    'id' => $contact->id,
                    'full_name' => (string) $contact->full_name,
                    'email' => (string) $contact->email,
                    'phone' => (string) $contact->phone,
                    'position' => (string) $contact->position,
                    'is_primary' => (bool) $contact->is_primary,
                    'note' => (string) $contact->note,
                ])
                ->values()
                ->all();

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
            'firstName' => [$this->isPersonType() ? 'required' : 'nullable', 'string', 'max:255'],
            'lastName' => [$this->isPersonType() ? 'required' : 'nullable', 'string', 'max:255'],
            'contacts' => ['array'],
            'contacts.*.id' => ['nullable', 'integer'],
            'contacts.*.full_name' => ['nullable', 'string', 'max:255'],
            'contacts.*.email' => ['nullable', 'email:rfc', 'max:255'],
            'contacts.*.phone' => ['nullable', 'string', 'max:255'],
            'contacts.*.position' => ['nullable', 'string', 'max:255'],
            'contacts.*.is_primary' => ['boolean'],
            'contacts.*.note' => ['nullable', 'string'],
        ];
    }

    public function addContact(): void
    {
        $this->contacts[] = [
            'id' => null,
            'full_name' => '',
            'email' => '',
            'phone' => '',
            'position' => '',
            'is_primary' => count($this->contacts) === 0,
            'note' => '',
        ];
    }

    public function removeContact(int $index): void
    {
        unset($this->contacts[$index]);
        $this->contacts = array_values($this->contacts);

        if ($this->contacts === []) {
            return;
        }

        if (! collect($this->contacts)->contains(fn (array $contact): bool => (bool) ($contact['is_primary'] ?? false))) {
            $this->contacts[0]['is_primary'] = true;
        }
    }

    public function markPrimaryContact(int $index): void
    {
        foreach ($this->contacts as $contactIndex => $contact) {
            $this->contacts[$contactIndex]['is_primary'] = $contactIndex === $index;
        }
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

            $contacts = collect($validated['contacts'] ?? [])
                ->map(function (array $contact): array {
                    return [
                        'id' => $contact['id'] ?? null,
                        'full_name' => trim((string) ($contact['full_name'] ?? '')),
                        'email' => trim((string) ($contact['email'] ?? '')),
                        'phone' => trim((string) ($contact['phone'] ?? '')),
                        'position' => trim((string) ($contact['position'] ?? '')),
                        'is_primary' => (bool) ($contact['is_primary'] ?? false),
                        'note' => trim((string) ($contact['note'] ?? '')),
                    ];
                })
                ->filter(fn (array $contact): bool => $contact['full_name'] !== ''
                    || $contact['email'] !== ''
                    || $contact['phone'] !== ''
                    || $contact['position'] !== ''
                    || $contact['note'] !== '')
                ->values();

            if ($contacts->contains(fn (array $contact): bool => $contact['full_name'] === '')) {
                $this->addError('contacts', 'Kontakt mora imati ime i prezime.');

                return;
            }

            if ($contacts->isNotEmpty() && ! $contacts->contains(fn (array $contact): bool => $contact['is_primary'])) {
                $contacts[0]['is_primary'] = true;
            }

            $primaryAssigned = false;
            $contacts = $contacts->map(function (array $contact) use (&$primaryAssigned): array {
                if (! $contact['is_primary'] || $primaryAssigned) {
                    $contact['is_primary'] = false;

                    return $contact;
                }

                $primaryAssigned = true;

                return $contact;
            });

            $existingContactIds = $client->contacts()->pluck('id')->all();
            $incomingContactIds = $contacts->pluck('id')->filter()->map(fn ($id): int => (int) $id)->all();

            $contactIdsForDeletion = array_diff($existingContactIds, $incomingContactIds);

            if ($contactIdsForDeletion !== []) {
                $client->contacts()->whereIn('id', $contactIdsForDeletion)->delete();
            }

            foreach ($contacts as $contact) {
                $contactId = $contact['id'] ? (int) $contact['id'] : null;
                unset($contact['id']);

                $client->contacts()->updateOrCreate(
                    ['id' => $contactId],
                    $contact
                );
            }

            $client->person()->delete();
        } elseif ($this->isPersonType()) {
            $client->person()->updateOrCreate([], [
                'first_name' => $validated['firstName'],
                'last_name' => $validated['lastName'],
            ]);
            $client->company()->delete();
            $client->contacts()->delete();
        } else {
            $client->company()->delete();
            $client->person()->delete();
            $client->contacts()->delete();
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

    public function isPersonType(): bool
    {
        if ($this->clientTypeId === '') {
            return false;
        }

        return ClientType::query()
            ->whereKey($this->clientTypeId)
            ->where('key', 'person')
            ->exists();
    }

    public function render(): View
    {
        return view('livewire.clients.form', [
            'clientTypes' => ClientType::query()->orderBy('id')->get(),
            'isEditing' => $this->clientId !== null,
            'isCompany' => $this->isCompanyType(),
            'isPerson' => $this->isPersonType(),
        ])->layout('layouts.app', [
            'title' => $this->clientId ? 'Izmena klijenta' : 'Novi klijent',
        ]);
    }
}
