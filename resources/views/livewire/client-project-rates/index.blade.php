<div class="flex h-full w-full flex-1 flex-col gap-6">
    @php($actionIconClass = 'size-3.5')

    <div class="flex flex-col gap-4 md:flex-row md:items-end md:justify-between">
        <div>
            <flux:heading size="xl">Cene klijenata</flux:heading>
            <flux:text>Klijent + projekat + period + cena kao jedan red.</flux:text>
        </div>

        <flux:button variant="primary" :href="route('client-project-rates.create')" wire:navigate>
            Dodaj
        </flux:button>
    </div>

    @if (session('status'))
        <flux:callout variant="success" icon="check-circle">{{ session('status') }}</flux:callout>
    @endif

    <div class="grid gap-3 md:grid-cols-3">
        <flux:input wire:model.live.debounce.300ms="search" label="Pretraga" placeholder="Klijent, projekat ili period" />

        <flux:select wire:model.live="statusFilter" label="Status">
            <option value="all">Svi</option>
            <option value="active">Aktivni</option>
            <option value="inactive">Neaktivni</option>
        </flux:select>
    </div>

    <x-ui.table>
        <x-ui.table.head>
            <tr>
                <x-ui.table.th>Klijent</x-ui.table.th>
                <x-ui.table.th>Projekat</x-ui.table.th>
                <x-ui.table.th>Period</x-ui.table.th>
                <x-ui.table.th>Cena</x-ui.table.th>
                <x-ui.table.th>Status</x-ui.table.th>
                <x-ui.table.th align="right">Akcija</x-ui.table.th>
            </tr>
        </x-ui.table.head>
        <x-ui.table.body>
                @forelse ($rates as $rate)
                    <x-ui.table.row wire:key="client-project-rate-{{ $rate->id }}">
                        <x-ui.table.td>
                            @if ($rate->client?->type?->key === 'person' && $rate->client?->person)
                                {{ trim($rate->client->person->first_name.' '.$rate->client->person->last_name) }}
                            @else
                                {{ $rate->client?->display_name }}
                            @endif
                        </x-ui.table.td>
                        <x-ui.table.td>
                            <div class="font-medium">{{ $rate->project?->name }}</div>
                            <div class="text-xs text-zinc-500">{{ $rate->project?->code }}</div>
                        </x-ui.table.td>
                        <x-ui.table.td>{{ $rate->billingPeriod?->name }}</x-ui.table.td>
                        <x-ui.table.td>{{ number_format((float) $rate->price_amount, 2, ',', '.') }} {{ $rate->currency }}</x-ui.table.td>
                        <x-ui.table.td>
                            @if ($rate->is_active)
                                <span class="inline-flex rounded-full bg-green-100 px-2 py-1 text-xs font-medium text-green-700 dark:bg-green-900/40 dark:text-green-300">Aktivna</span>
                            @else
                                <span class="inline-flex rounded-full bg-zinc-100 px-2 py-1 text-xs font-medium text-zinc-700 dark:bg-zinc-800 dark:text-zinc-300">Neaktivna</span>
                            @endif
                        </x-ui.table.td>
                        <x-ui.table.td align="right">
                            <x-ui.table.actions>
                                <x-ui.buttons.icon-action
                                    :href="route('client-project-rates.edit', $rate)"
                                    title="Izmeni cenu"
                                    color="primary"
                                    navigate
                                >
                                    <x-ui.icons.pen :class="$actionIconClass" />
                                </x-ui.buttons.icon-action>

                                <x-ui.buttons.icon-action
                                    wire:click="toggleActive({{ $rate->id }})"
                                    :title="$rate->is_active ? 'Deaktiviraj cenu' : 'Aktiviraj cenu'"
                                    color="warning"
                                >
                                    <x-ui.icons.disable :class="$actionIconClass" />
                                </x-ui.buttons.icon-action>

                                <x-ui.buttons.icon-action
                                    wire:click="deleteRate({{ $rate->id }})"
                                    title="Obriši cenu"
                                    color="danger"
                                >
                                    <x-ui.icons.trash :class="$actionIconClass" />
                                </x-ui.buttons.icon-action>
                            </x-ui.table.actions>
                        </x-ui.table.td>
                    </x-ui.table.row>
                @empty
                    <x-ui.table.empty colspan="6">
                        Nema unetih cena klijenata.
                    </x-ui.table.empty>
                @endforelse
        </x-ui.table.body>
    </x-ui.table>

    <div>
        {{ $rates->links() }}
    </div>
</div>
