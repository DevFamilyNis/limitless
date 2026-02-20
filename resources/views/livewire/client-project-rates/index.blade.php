<div class="flex h-full w-full flex-1 flex-col gap-6">
    @php($actionIconClass = 'size-4')

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

    <div class="overflow-hidden rounded-xl border border-zinc-200 dark:border-zinc-700">
        <table class="w-full text-sm">
            <thead class="bg-zinc-50 dark:bg-zinc-900/40">
                <tr>
                    <th class="px-4 py-3 text-left">Klijent</th>
                    <th class="px-4 py-3 text-left">Projekat</th>
                    <th class="px-4 py-3 text-left">Period</th>
                    <th class="px-4 py-3 text-left">Cena</th>
                    <th class="px-4 py-3 text-left">Status</th>
                    <th class="px-4 py-3 text-right">Akcija</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($rates as $rate)
                    <tr wire:key="client-project-rate-{{ $rate->id }}" class="border-t border-zinc-200 dark:border-zinc-700">
                        <td class="px-4 py-3">
                            @if ($rate->client?->type?->key === 'person' && $rate->client?->person)
                                {{ trim($rate->client->person->first_name.' '.$rate->client->person->last_name) }}
                            @else
                                {{ $rate->client?->display_name }}
                            @endif
                        </td>
                        <td class="px-4 py-3">
                            <div class="font-medium">{{ $rate->project?->name }}</div>
                            <div class="text-xs text-zinc-500">{{ $rate->project?->code }}</div>
                        </td>
                        <td class="px-4 py-3">{{ $rate->billingPeriod?->name }}</td>
                        <td class="px-4 py-3">{{ number_format((float) $rate->price_amount, 2, ',', '.') }} {{ $rate->currency }}</td>
                        <td class="px-4 py-3">
                            @if ($rate->is_active)
                                <span class="inline-flex rounded-full bg-green-100 px-2 py-1 text-xs font-medium text-green-700 dark:bg-green-900/40 dark:text-green-300">Aktivna</span>
                            @else
                                <span class="inline-flex rounded-full bg-zinc-100 px-2 py-1 text-xs font-medium text-zinc-700 dark:bg-zinc-800 dark:text-zinc-300">Neaktivna</span>
                            @endif
                        </td>
                        <td class="px-4 py-3">
                            <div class="flex items-center justify-end gap-2">
                                <flux:button
                                    size="sm"
                                    variant="filled"
                                    class="size-9 p-0"
                                    :href="route('client-project-rates.edit', $rate)"
                                    wire:navigate
                                    title="Izmeni cenu"
                                >
                                    <x-ui.icons.pen :class="$actionIconClass" />
                                </flux:button>

                                <flux:button
                                    size="sm"
                                    variant="filled"
                                    class="size-9 p-0"
                                    wire:click="toggleActive({{ $rate->id }})"
                                    :title="$rate->is_active ? 'Deaktiviraj cenu' : 'Aktiviraj cenu'"
                                >
                                    <x-ui.icons.disable :class="$actionIconClass" />
                                </flux:button>

                                <flux:button
                                    size="sm"
                                    variant="danger"
                                    class="size-9 p-0"
                                    wire:click="deleteRate({{ $rate->id }})"
                                    title="Obriši cenu"
                                >
                                    <x-ui.icons.trash :class="$actionIconClass" />
                                </flux:button>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td class="px-4 py-6 text-center text-zinc-500" colspan="6">
                            Nema unetih cena klijenata.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div>
        {{ $rates->links() }}
    </div>
</div>
