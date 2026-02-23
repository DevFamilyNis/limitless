<div class="flex h-full w-full flex-1 flex-col gap-6">
    <div class="flex flex-col gap-4 md:flex-row md:items-end md:justify-between">
        <div>
            <flux:heading size="xl">Transakcije</flux:heading>
            <flux:text>Read-only pregled prihoda i rashoda po mesecu.</flux:text>
        </div>
    </div>

    @if (session('status'))
        <flux:callout variant="success" icon="check-circle">{{ session('status') }}</flux:callout>
    @endif

    <div class="grid gap-3 md:grid-cols-4">
        <flux:select wire:model.live="month" label="Mesec">
            @foreach ($months as $monthKey => $monthName)
                <option value="{{ $monthKey }}">{{ ucfirst($monthName) }}</option>
            @endforeach
        </flux:select>
        <flux:select wire:model.live="year" label="Godina">
            @foreach ($years as $yearKey => $yearName)
                <option value="{{ $yearKey }}">{{ $yearName }}</option>
            @endforeach
        </flux:select>
        <flux:input wire:model.live.debounce.300ms="search" label="Pretraga" placeholder="Naziv, kategorija, klijent..." />
        <flux:select wire:model.live="typeFilter" label="Tip">
            <option value="all">Svi</option>
            <option value="income">Prihod</option>
            <option value="expense">Rashod</option>
        </flux:select>
    </div>

    <x-ui.table>
        <x-ui.table.head>
            <tr>
                <x-ui.table.th>Datum</x-ui.table.th>
                <x-ui.table.th>Naslov</x-ui.table.th>
                <x-ui.table.th>Kategorija</x-ui.table.th>
                <x-ui.table.th>Dokument</x-ui.table.th>
                <x-ui.table.th>Iznos</x-ui.table.th>
            </tr>
        </x-ui.table.head>
        <x-ui.table.body>
            @forelse ($transactions as $transaction)
                <x-ui.table.row wire:key="transaction-{{ $transaction->id }}">
                    <x-ui.table.td>{{ $transaction->date?->format('d.m.Y') }}</x-ui.table.td>
                    <x-ui.table.td>
                        <div class="font-medium">{{ $transaction->title }}</div>
                        @if ($transaction->client)
                            <div class="text-xs text-zinc-500">Klijent: {{ $transaction->client->display_name }}</div>
                        @endif
                    </x-ui.table.td>
                    <x-ui.table.td>
                        <div>{{ $transaction->category?->name }}</div>
                        <div class="text-xs text-zinc-500">
                            {{ $transaction->category?->type?->key === 'expense' ? 'Rashod' : $transaction->category?->type?->name }}
                        </div>
                    </x-ui.table.td>
                    <x-ui.table.td>
                        @if ($transaction->invoice)
                            Faktura {{ $transaction->invoice->invoice_number }}
                        @else
                            Fiskalni račun
                        @endif
                    </x-ui.table.td>
                    <x-ui.table.td>{{ number_format((float) $transaction->amount, 2, ',', '.') }} {{ $transaction->currency }}</x-ui.table.td>
                </x-ui.table.row>
            @empty
                <x-ui.table.empty colspan="5">
                    Nema transakcija za izabrani period.
                </x-ui.table.empty>
            @endforelse
        </x-ui.table.body>
    </x-ui.table>

    <div>
        {{ $transactions->links() }}
    </div>
</div>
