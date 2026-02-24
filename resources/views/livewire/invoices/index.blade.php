<div class="flex h-full w-full flex-1 flex-col gap-6">
    @php($actionIconClass = 'size-3.5')

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

    <x-ui.table>
        <x-ui.table.head>
            <tr>
                <x-ui.table.th>Broj</x-ui.table.th>
                <x-ui.table.th>Klijent</x-ui.table.th>
                <x-ui.table.th>Datumi</x-ui.table.th>
                <x-ui.table.th>Iznosi</x-ui.table.th>
                <x-ui.table.th>Status</x-ui.table.th>
                <x-ui.table.th align="right">Akcija</x-ui.table.th>
            </tr>
        </x-ui.table.head>
        <x-ui.table.body>
                @forelse ($invoices as $invoice)
                    @php($isOverdue = $invoice->due_date && $invoice->due_date->isPast() && ! in_array($invoice->status?->key, ['paid', 'canceled'], true))
                    <x-ui.table.row wire:key="invoice-{{ $invoice->id }}" :highlight="$isOverdue">
                        <x-ui.table.td>
                            <div class="font-medium">{{ $invoice->invoice_number }}</div>
                            <div class="text-xs text-zinc-500">#{{ $invoice->invoice_seq }} / {{ $invoice->invoice_year }}</div>
                        </x-ui.table.td>
                        <x-ui.table.td>
                            @if ($invoice->client?->type?->key === 'person' && $invoice->client?->person)
                                {{ trim($invoice->client->person->first_name.' '.$invoice->client->person->last_name) }}
                            @else
                                {{ $invoice->client?->display_name }}
                            @endif
                        </x-ui.table.td>
                        <x-ui.table.td>
                            <div>Promet: {{ $invoice->issue_date?->format('d.m.Y') }}</div>
                            <div class="text-xs text-zinc-500">
                                Dospeće: {{ $invoice->due_date?->format('d.m.Y') ?? '-' }}
                            </div>
                        </x-ui.table.td>
                        <x-ui.table.td>
                            <div>Ukupno: {{ number_format((float) $invoice->total, 2, ',', '.') }}</div>
                            <div class="text-xs text-zinc-500">Stavki: {{ $invoice->items_count }}</div>
                        </x-ui.table.td>
                        <x-ui.table.td>
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
                        </x-ui.table.td>
                        <x-ui.table.td align="right">
                            <x-ui.table.actions>
                                @if ($statusKey !== 'paid')
                                    <x-ui.buttons.icon-action
                                        wire:click="markAsPaid({{ $invoice->id }})"
                                        title="Označi kao plaćenu"
                                        color="success"
                                    >
                                        <x-ui.icons.check :class="$actionIconClass" />
                                    </x-ui.buttons.icon-action>
                                @endif

                                <x-ui.buttons.icon-action
                                    wire:click="downloadPdf({{ $invoice->id }})"
                                    title="Preuzmi PDF"
                                    color="primary"
                                >
                                    <x-ui.icons.download :class="$actionIconClass" />
                                </x-ui.buttons.icon-action>

                                <x-ui.buttons.icon-action
                                    :href="route('invoices.edit', $invoice)"
                                    title="Izmeni fakturu"
                                    color="primary"
                                    navigate
                                >
                                    <x-ui.icons.pen :class="$actionIconClass" />
                                </x-ui.buttons.icon-action>

                                <x-ui.buttons.icon-action
                                    wire:click="deleteInvoice({{ $invoice->id }})"
                                    title="Obriši fakturu"
                                    color="danger"
                                >
                                    <x-ui.icons.trash :class="$actionIconClass" />
                                </x-ui.buttons.icon-action>
                            </x-ui.table.actions>
                        </x-ui.table.td>
                    </x-ui.table.row>
                @empty
                    <x-ui.table.empty colspan="6">
                        Nema unetih faktura.
                    </x-ui.table.empty>
                @endforelse
        </x-ui.table.body>
    </x-ui.table>

    <div>
        {{ $invoices->links() }}
    </div>
</div>
