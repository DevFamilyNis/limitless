<?php

namespace App\Livewire\Clients;

use App\Domain\Clients\Actions\UpsertClientAction;
use App\Domain\Clients\DTO\UpsertClientData;
use App\Models\Client;
use App\Models\ClientType;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
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
        if ($this->isPersonType()) {
            $fullName = trim($this->firstName.' '.$this->lastName);

            if ($fullName !== '' && trim($this->displayName) === '') {
                $this->displayName = $fullName;
            }
        }

        $validated = $this->validate();

        try {
            $client = app(UpsertClientAction::class)->execute(
                UpsertClientData::fromArray([
                    'user_id' => Auth::id(),
                    'client_id' => $this->clientId,
                    'client_type_id' => (int) $validated['clientTypeId'],
                    'display_name' => $validated['displayName'],
                    'email' => $validated['email'] ?? null,
                    'phone' => $validated['phone'] ?? null,
                    'address' => $validated['address'] ?? null,
                    'note' => $validated['note'] ?? null,
                    'pib' => $validated['pib'] ?? null,
                    'mb' => $validated['mb'] ?? null,
                    'bank_account' => $validated['bankAccount'] ?? null,
                    'first_name' => $validated['firstName'] ?? null,
                    'last_name' => $validated['lastName'] ?? null,
                    'contacts' => $validated['contacts'] ?? [],
                ])
            );
        } catch (ValidationException $exception) {
            foreach ($exception->errors() as $field => $messages) {
                foreach ($messages as $message) {
                    $this->addError($field, $message);
                }
            }

            return;
        }

        $message = $client->wasRecentlyCreated
            ? __('messages.clients.flash_created')
            : __('messages.clients.flash_updated');

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
            'title' => $this->clientId ? __('messages.clients.form_edit_title') : __('messages.clients.form_new_title'),
        ]);
    }
}
