<div class="flex h-full w-full flex-1 flex-col gap-6">
    <div class="flex flex-col gap-4 md:flex-row md:items-end md:justify-between">
        <div>
            <flux:heading size="xl">@lang('messages.text.clients')</flux:heading>
            <flux:text>@lang('messages.text.clientSubTitle')</flux:text>
        </div>

        <flux:button variant="primary" :href="route('clients.create')" wire:navigate>
            @lang('messages.buttons.add')
        </flux:button>
    </div>

    @if (session('status'))
        <flux:callout variant="success" icon="check-circle">{{ session('status') }}</flux:callout>
    @endif

    @if (session('error'))
        <flux:callout variant="danger" icon="x-circle">{{ session('error') }}</flux:callout>
    @endif

    <div class="grid gap-3 md:grid-cols-3">
        <flux:input
            wire:model.live.debounce.300ms="search"
            label="Pretraga"
            placeholder="Naziv, email ili telefon"
        />

        <flux:select wire:model.live="statusFilter" label="Status">
            <option value="all">Svi</option>
            <option value="active">Aktivni</option>
            <option value="inactive">Neaktivni</option>
        </flux:select>
    </div>

    <div class="overflow-hidden rounded-xl border border-zinc-200 dark:border-zinc-700">
        <table class="w-full text-sm">
            <thead class="bg-zinc-50 dark:bg-zinc-900/40">
                <tr>
                    <th class="px-4 py-3 text-left">Naziv</th>
                    <th class="px-4 py-3 text-left">Tip</th>
                    <th class="px-4 py-3 text-left">Kontakt</th>
                    <th class="px-4 py-3 text-left">Status</th>
                    <th class="px-4 py-3 text-right">Akcije</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($clients as $client)
                    <tr wire:key="client-{{ $client->id }}" class="border-t border-zinc-200 dark:border-zinc-700">
                        <td class="px-4 py-3">
                            <div class="font-medium">{{ $client->display_name }}</div>
                            @if ($client->company && $client->company->pib)
                                <div class="text-xs text-zinc-500">PIB: {{ $client->company->pib }}</div>
                            @endif
                        </td>
                        <td class="px-4 py-3">{{ $client->type->name }}</td>
                        <td class="px-4 py-3">
                            <div>{{ $client->email ?: '-' }}</div>
                            <div class="text-xs text-zinc-500">{{ $client->phone ?: '-' }}</div>
                        </td>
                        <td class="px-4 py-3">
                            @if ($client->is_active)
                                <span class="inline-flex rounded-full bg-green-100 px-2 py-1 text-xs font-medium text-green-700 dark:bg-green-900/40 dark:text-green-300">Aktivan</span>
                            @else
                                <span class="inline-flex rounded-full bg-zinc-100 px-2 py-1 text-xs font-medium text-zinc-700 dark:bg-zinc-800 dark:text-zinc-300">Neaktivan</span>
                            @endif
                        </td>
                        <td class="px-4 py-3">
                            <div class="flex items-center justify-end gap-2">
                                <flux:button size="sm" variant="ghost" :href="route('clients.edit', $client)" wire:navigate>
                                    Izmeni
                                </flux:button>

                                <flux:button
                                    size="sm"
                                    variant="subtle"
                                    wire:click="toggleActive({{ $client->id }})"
                                >
                                    {{ $client->is_active ? 'Deaktiviraj' : 'Aktiviraj' }}
                                </flux:button>

                                <flux:button
                                    size="sm"
                                    variant="danger"
                                    wire:click="deleteClient({{ $client->id }})"
                                >
                                    Obriši
                                </flux:button>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td class="px-4 py-6 text-center text-zinc-500" colspan="5">
                            Nema klijenata za prikaz.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div>
        {{ $clients->links() }}
    </div>
</div>
