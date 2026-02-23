<div class="flex h-full w-full flex-1 flex-col gap-6">
    @php($actionIconClass = 'size-4')

    <div class="flex flex-col gap-4 md:flex-row md:items-end md:justify-between">
        <div>
            <flux:heading size="xl">Fakture</flux:heading>
            <flux:text>Pregled, pretraga i upravljanje statusima faktura.</flux:text>
        </div>

        <flux:button variant="primary" :href="route('invoices.create')" wire:navigate>
            Dodaj
        </flux:button>
    </div>

    @if (session('status'))
        <flux:callout variant="success" icon="check-circle">{{ session('status') }}</flux:callout>
    @endif

    <div class="grid gap-3 md:grid-cols-3">
        <flux:input wire:model.live.debounce.300ms="search" label="Pretraga" placeholder="Broj fakture, klijent ili napomena" />

        <flux:select wire:model.live="statusFilter" label="Status">
            <option value="all">Svi</option>
            @foreach ($statuses as $status)
                <option value="{{ $status->id }}">{{ $status->name }}</option>
            @endforeach
        </flux:select>
    </div>

    <div class="overflow-hidden rounded-xl border border-zinc-200 dark:border-zinc-700">
        <table class="w-full text-sm">
            <thead class="bg-zinc-50 dark:bg-zinc-900/40">
                <tr>
                    <th class="px-4 py-3 text-left">Broj</th>
                    <th class="px-4 py-3 text-left">Klijent</th>
                    <th class="px-4 py-3 text-left">Datumi</th>
                    <th class="px-4 py-3 text-left">Iznosi</th>
                    <th class="px-4 py-3 text-left">Status</th>
                    <th class="px-4 py-3 text-right">Akcija</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($invoices as $invoice)
                    <tr wire:key="invoice-{{ $invoice->id }}" class="border-t border-zinc-200 dark:border-zinc-700">
                        <td class="px-4 py-3">
                            <div class="font-medium">{{ $invoice->invoice_number }}</div>
                            <div class="text-xs text-zinc-500">#{{ $invoice->invoice_seq }} / {{ $invoice->invoice_year }}</div>
                        </td>
                        <td class="px-4 py-3">
                            @if ($invoice->client?->type?->key === 'person' && $invoice->client?->person)
                                {{ trim($invoice->client->person->first_name.' '.$invoice->client->person->last_name) }}
                            @else
                                {{ $invoice->client?->display_name }}
                            @endif
                        </td>
                        <td class="px-4 py-3">
                            <div>Promet: {{ $invoice->issue_date?->format('d.m.Y') }}</div>
                            <div class="text-xs text-zinc-500">
                                Dospeće: {{ $invoice->due_date?->format('d.m.Y') ?? '-' }}
                            </div>
                        </td>
                        <td class="px-4 py-3">
                            <div>Ukupno: {{ number_format((float) $invoice->total, 2, ',', '.') }}</div>
                            <div class="text-xs text-zinc-500">Stavki: {{ $invoice->items_count }}</div>
                        </td>
                        <td class="px-4 py-3">
                            @php($statusKey = $invoice->status?->key)
                            @php($statusClasses = match ($statusKey) {
                                'paid' => 'bg-green-100 text-green-700 dark:bg-green-900/40 dark:text-green-300',
                                'sent' => 'bg-blue-100 text-blue-700 dark:bg-blue-900/40 dark:text-blue-300',
                                'canceled' => 'bg-red-100 text-red-700 dark:bg-red-900/40 dark:text-red-300',
                                default => 'bg-zinc-100 text-zinc-700 dark:bg-zinc-800 dark:text-zinc-300',
                            })
                            <span class="inline-flex rounded-full px-2 py-1 text-xs font-medium {{ $statusClasses }}">
                                {{ $invoice->status?->name ?? '-' }}
                            </span>
                        </td>
                        <td class="px-4 py-3">
                            <div class="flex items-center justify-end gap-2">
                                @if ($statusKey !== 'paid')
                                    <flux:button
                                        size="sm"
                                        variant="filled"
                                        wire:click="markAsPaid({{ $invoice->id }})"
                                        title="Označi kao plaćenu"
                                    >
                                        Plaćena
                                    </flux:button>
                                @endif

                                <flux:button
                                    size="sm"
                                    variant="filled"
                                    class="size-9 p-0"
                                    :href="route('invoices.edit', $invoice)"
                                    wire:navigate
                                    title="Izmeni fakturu"
                                >
                                    <x-ui.icons.pen :class="$actionIconClass" />
                                </flux:button>

                                <flux:button
                                    size="sm"
                                    variant="danger"
                                    class="size-9 p-0"
                                    wire:click="deleteInvoice({{ $invoice->id }})"
                                    title="Obriši fakturu"
                                >
                                    <x-ui.icons.trash :class="$actionIconClass" />
                                </flux:button>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td class="px-4 py-6 text-center text-zinc-500" colspan="6">
                            Nema unetih faktura.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div>
        {{ $invoices->links() }}
    </div>
</div>
