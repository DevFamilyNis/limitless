<div class="flex h-full w-full flex-1 flex-col gap-6">
    <div class="mb-6 flex items-center justify-between">
        <div>
            <flux:heading size="xl">{{ $isEditing ? 'Izmena fakture' : 'Nova faktura' }}</flux:heading>
            <flux:text>Unesi podatke o fakturi. Broj je read-only i automatski dodeljen.</flux:text>
        </div>

        <flux:button variant="ghost" :href="route('invoices.index')" wire:navigate>
            Nazad
        </flux:button>
    </div>

    @unless ($hasRequiredData)
        <flux:callout variant="warning" icon="exclamation-triangle" class="mb-6">
            Za unos fakture potrebno je da imaš bar jednog aktivnog klijenta i jednu uslugu.
        </flux:callout>
    @endunless

    <form wire:submit="save" class="space-y-6">
        <div class="grid gap-4 md:grid-cols-3">
            <flux:input wire:model="invoiceNumber" label="Broj fakture" readonly />
            <flux:input wire:model="invoiceYear" label="Godina" readonly />
            <flux:input wire:model="invoiceSeq" label="Sekvenca" readonly />
        </div>

        <div class="grid gap-4 md:grid-cols-2">
            <flux:select wire:model.live="clientId" label="Klijent" required>
                <option value="">Izaberi klijenta</option>
                @foreach ($clients as $client)
                    <option value="{{ $client->id }}">
                        @if ($client->type?->key === 'person' && $client->person)
                            {{ trim($client->person->first_name.' '.$client->person->last_name) }}
                        @else
                            {{ $client->display_name }}
                        @endif
                    </option>
                @endforeach
            </flux:select>

            <flux:select wire:model.live="statusId" label="Status" required>
                <option value="">Izaberi status</option>
                @foreach ($statuses as $status)
                    <option value="{{ $status->id }}">{{ $status->name }}</option>
                @endforeach
            </flux:select>
        </div>

        <div class="grid gap-4 md:grid-cols-2">
            <flux:input wire:model="issueDate" label="Datum prometa" type="date" required />
            <flux:input wire:model="dueDate" label="Datum dospeća" type="date" />
        </div>

        <div>
            <flux:heading size="lg" class="mb-3">Usluge</flux:heading>

            @if (! $hasClientPriceList)
                <flux:callout variant="warning" icon="exclamation-triangle">
                    Izabrani klijent nema cenovnik. Prvo dodaj usluge u cenovnik klijenta.
                </flux:callout>
            @else
                <div class="overflow-hidden rounded-lg border border-zinc-200 dark:border-zinc-700">
                    <table class="w-full text-sm">
                        <thead class="bg-zinc-50 dark:bg-zinc-900/40">
                            <tr>
                                <th class="px-4 py-3 text-left">Usluga</th>
                                <th class="px-4 py-3 text-left">Količina</th>
                                <th class="px-4 py-3 text-left">Cena</th>
                                <th class="px-4 py-3 text-left">Iznos</th>
                                <th class="px-4 py-3 text-right">Akcija</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($items as $index => $item)
                                <tr wire:key="invoice-item-{{ $item['id'] ?? 'new-'.$index }}" class="border-t border-zinc-200 dark:border-zinc-700">
                                    <td class="px-4 py-3">{{ $item['description'] }}</td>
                                    <td class="px-4 py-3">{{ number_format((float) $item['quantity'], 2, ',', '.') }}</td>
                                    <td class="px-4 py-3">{{ number_format((float) $item['unitPrice'], 2, ',', '.') }}</td>
                                    <td class="px-4 py-3">{{ number_format((float) $item['amount'], 2, ',', '.') }}</td>
                                    <td class="px-4 py-3 text-right">
                                        <flux:button
                                            type="button"
                                            size="sm"
                                            variant="danger"
                                            class="size-9 p-0"
                                            wire:click="removeItem({{ $index }})"
                                            title="Ukloni stavku"
                                        >
                                            <x-ui.icons.trash class="size-4" />
                                        </flux:button>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>

        <flux:input wire:model="total" label="Ukupno" readonly />

        <flux:textarea wire:model="note" label="Napomena" rows="3" />

        <div class="flex items-center gap-3">
            <flux:button variant="primary" type="submit" :disabled="! $hasRequiredData">
                Sačuvaj
            </flux:button>
        </div>
    </form>
</div>
