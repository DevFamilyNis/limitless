<div class="mx-auto w-full max-w-3xl">
    <div class="mb-6 flex items-center justify-between">
        <div>
            <flux:heading size="xl">{{ $isEditing ? 'Izmena korisnika' : 'Novi korisnik' }}</flux:heading>
            <flux:text>@lang('messages.text.infoAboutClient')</flux:text>
        </div>

        <flux:button variant="ghost" :href="route('clients.index')" wire:navigate>
           @lang('messages.buttons.back')
        </flux:button>
    </div>

    <form wire:submit="save" class="space-y-6 rounded-xl border border-zinc-200 p-6 dark:border-zinc-700">
        <flux:select wire:model.live="clientTypeId" :label="__('messages.form.category')" required>
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
                <flux:input wire:model="firstName" :label="__('messages.form.name')" required />
                <flux:input wire:model="lastName" :label="__('messages.form.surname')" required />
            </div>
        @endif

        @if ($isCompany)
            <div class="space-y-4 rounded-xl border border-zinc-200 p-4 dark:border-zinc-700">
                <div class="flex items-center justify-between">
                    <flux:heading size="lg">@lang('messages.clients.app_links')</flux:heading>
                    <flux:button variant="subtle" type="button" wire:click="addAppLink">
                        @lang('messages.buttons.add')
                    </flux:button>
                </div>

                @error('app_links')
                    <flux:text class="text-red-600 dark:text-red-400">{{ $message }}</flux:text>
                @enderror

                @forelse ($appLinks as $index => $appLink)
                    <div wire:key="app-link-row-{{ $appLink['id'] ?? 'new-'.$index }}" class="grid gap-3 rounded-lg p-4 md:grid-cols-12">
                        <div class="md:col-span-4">
                            <flux:input wire:model="appLinks.{{ $index }}.label" :label="__('messages.form.name')" />
                        </div>
                        <div class="md:col-span-7">
                            <flux:input wire:model="appLinks.{{ $index }}.url" :label="__('messages.form.link')" type="url" placeholder="https://app.example.com" />
                        </div>
                        <div class="flex items-end md:col-span-1">
                            <flux:button type="button" variant="danger" wire:click="removeAppLink({{ $index }})" class="w-full">
                                @lang('messages.buttons.delete')
                            </flux:button>
                        </div>
                    </div>
                @empty
                    <flux:text class="text-zinc-500">@lang('messages.clients.no_app_links')</flux:text>
                @endforelse
            </div>

            <div class="grid gap-4 md:grid-cols-3">
                <flux:input wire:model="pib" :label="__('messages.form.pib')" required />
                <flux:input wire:model="mb" :label="__('messages.form.mb')" required />
                <flux:input wire:model="bankAccount" :label="__('messages.form.account')" required />
            </div>

            <div class="space-y-4 rounded-xl border border-zinc-200 p-4 dark:border-zinc-700">
                <div class="flex items-center justify-between">
                    <flux:heading size="lg">@lang('messages.text.companyContacts')</flux:heading>
                    <flux:button variant="subtle" type="button" wire:click="addContact">
                       @lang('messages.buttons.add')
                    </flux:button>
                </div>

                @error('contacts')
                    <flux:text class="text-red-600 dark:text-red-400">{{ $message }}</flux:text>
                @enderror

                @forelse ($contacts as $index => $contact)
                    <div wire:key="contact-row-{{ $contact['id'] ?? 'new-'.$index }}" class="space-y-3 rounded-lg  p-4 dark:border-zinc-700">
                        <div class="grid gap-3 md:grid-cols-2">
                            <flux:input wire:model="contacts.{{ $index }}.full_name"
                                        :label="__('messages.form.nameSurname')" />
                            <flux:input wire:model="contacts.{{ $index }}.position"
                                        :label="__('messages.form.position')" />
                        </div>

                        <div class="grid gap-3 md:grid-cols-2">
                            <flux:input wire:model="contacts.{{ $index }}.email" type="email"
                                        :label="__('messages.form.email')" />
                            <flux:input wire:model="contacts.{{ $index }}.phone"
                                        :label="__('messages.form.phone')" />
                        </div>

                        <flux:textarea wire:model="contacts.{{ $index }}.note" rows="2"
                                       :label="__('messages.form.note')" />

                        <div class="flex items-center gap-3">
                            <flux:button
                                type="button"
                                variant="{{ ($contact['is_primary'] ?? false) ? 'outline' : 'ghost' }}"
                                wire:click="markPrimaryContact({{ $index }})"
                            >
                                {{ ($contact['is_primary'] ?? false) ? 'Glavni kontakt' : 'Postavi kao glavni' }}
                            </flux:button>

                            <flux:button type="button" variant="danger" wire:click="removeContact({{ $index }})">
                                @lang('messages.buttons.delete')
                            </flux:button>
                        </div>
                    </div>
                @empty
                    <flux:text class="text-zinc-500">@lang('messages.text.noContacts')</flux:text>
                @endforelse
            </div>
        @endif

        <div class="flex items-center gap-3">
            <flux:button variant="primary" type="submit">
                @lang('messages.buttons.save')
            </flux:button>
        </div>
    </form>
</div>
