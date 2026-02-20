<div class="mx-auto w-full max-w-3xl">
    <div class="mb-6 flex items-center justify-between">
        <div>
            <flux:heading size="xl">{{ $isEditing ? 'Izmena klijenta' : 'Novi klijent' }}</flux:heading>
            <flux:text>@lang('messages.text.infoAboutClient')</flux:text>
        </div>

        <flux:button variant="ghost" :href="route('clients.index')" wire:navigate>
           @lang('messages.buttons.back')
        </flux:button>
    </div>

    <form wire:submit="save" class="space-y-6 rounded-xl border border-zinc-200 p-6 dark:border-zinc-700">
        <flux:select wire:model.live="clientTypeId" label="Tip" required>
            @foreach ($clientTypes as $type)
                <option value="{{ $type->id }}">{{ $type->name }}</option>
            @endforeach
        </flux:select>
        @if ($isCompany)
            <flux:input wire:model="displayName" :label="__('messages.table.name')" required />
        @endif
        <div class="grid gap-4 md:grid-cols-2">
            <flux:input wire:model="email" :label="__('messages.form.email')" type="email" />
            <flux:input wire:model="phone" :label="__('messages.form.phone')" />
        </div>

        <flux:input wire:model="address" :label="__('messages.form.address')" />
        <flux:textarea wire:model="note" :label="__('messages.form.note')" rows="3" />

        @if ($isPerson)
            <div class="grid gap-4 md:grid-cols-2">
                <flux:input wire:model="firstName" label="Ime" required />
                <flux:input wire:model="lastName" label="Prezime" required />
            </div>
        @endif

        @if ($isCompany)
            <div class="grid gap-4 md:grid-cols-3">
                <flux:input wire:model="pib" label="PIB" required />
                <flux:input wire:model="mb" label="Matični broj" required />
                <flux:input wire:model="bankAccount" label="Račun" required />
            </div>

            <div class="space-y-4 rounded-xl border border-zinc-200 p-4 dark:border-zinc-700">
                <div class="flex items-center justify-between">
                    <flux:heading size="lg">Kontakti firme</flux:heading>
                    <flux:button variant="subtle" type="button" wire:click="addContact">
                        Dodaj kontakt
                    </flux:button>
                </div>

                @error('contacts')
                    <flux:text class="text-red-600 dark:text-red-400">{{ $message }}</flux:text>
                @enderror

                @forelse ($contacts as $index => $contact)
                    <div wire:key="contact-row-{{ $contact['id'] ?? 'new-'.$index }}" class="space-y-3 rounded-lg border border-zinc-200 p-4 dark:border-zinc-700">
                        <div class="grid gap-3 md:grid-cols-2">
                            <flux:input wire:model="contacts.{{ $index }}.full_name" label="Ime i prezime" />
                            <flux:input wire:model="contacts.{{ $index }}.position" label="Pozicija" />
                        </div>

                        <div class="grid gap-3 md:grid-cols-2">
                            <flux:input wire:model="contacts.{{ $index }}.email" type="email" label="Email" />
                            <flux:input wire:model="contacts.{{ $index }}.phone" label="Telefon" />
                        </div>

                        <flux:textarea wire:model="contacts.{{ $index }}.note" rows="2" label="Napomena" />

                        <div class="flex items-center gap-3">
                            <flux:button
                                type="button"
                                variant="{{ ($contact['is_primary'] ?? false) ? 'primary' : 'ghost' }}"
                                wire:click="markPrimaryContact({{ $index }})"
                            >
                                {{ ($contact['is_primary'] ?? false) ? 'Glavni kontakt' : 'Postavi kao glavni' }}
                            </flux:button>

                            <flux:button type="button" variant="danger" wire:click="removeContact({{ $index }})">
                                Ukloni
                            </flux:button>
                        </div>
                    </div>
                @empty
                    <flux:text class="text-zinc-500">Nema dodatih kontakata.</flux:text>
                @endforelse
            </div>
        @endif

        <div class="flex items-center gap-3">
            <flux:button variant="primary" type="submit">
                Sačuvaj
            </flux:button>
        </div>
    </form>
</div>
