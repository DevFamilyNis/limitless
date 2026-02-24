<div class="mx-auto flex w-full max-w-6xl flex-col gap-6">
    <div class="flex flex-col gap-4 md:flex-row md:items-start md:justify-between">
        <div>
            <flux:heading size="xl">{{ $clientName }}</flux:heading>
            <flux:text>Detaljan pregled klijenta i povezanih podataka.</flux:text>
        </div>

        <div class="flex items-center gap-2">
            <flux:button variant="ghost" :href="route('clients.index')" wire:navigate>
                Nazad
            </flux:button>
            <flux:button variant="primary" :href="route('clients.edit', $client)" wire:navigate>
                Izmeni
            </flux:button>
        </div>
    </div>

    <div class="grid gap-4 md:grid-cols-4">
        <flux:card>
            <flux:text class="text-xs text-zinc-500">Fakture</flux:text>
            <flux:heading size="lg">{{ $client->invoices_count }}</flux:heading>
        </flux:card>
        <flux:card>
            <flux:text class="text-xs text-zinc-500">Transakcije</flux:text>
            <flux:heading size="lg">{{ $client->transactions_count }}</flux:heading>
        </flux:card>
        <flux:card>
            <flux:text class="text-xs text-zinc-500">Cenovnik stavki</flux:text>
            <flux:heading size="lg">{{ $client->project_rates_count }}</flux:heading>
        </flux:card>
        <flux:card>
            <flux:text class="text-xs text-zinc-500">Issues</flux:text>
            <flux:heading size="lg">{{ $client->issues_count }}</flux:heading>
        </flux:card>
    </div>

    <div class="grid gap-6 lg:grid-cols-2">
        <flux:card class="space-y-3">
            <flux:heading size="lg">Osnovni podaci</flux:heading>

            <div class="grid gap-3 text-sm">
                <div><span class="text-zinc-500">Tip:</span> {{ $client->type?->name ?? '-' }}</div>
                <div><span class="text-zinc-500">Status:</span> {{ $client->is_active ? 'Aktivan' : 'Neaktivan' }}</div>
                <div><span class="text-zinc-500">Email:</span> {{ $client->email ?: '-' }}</div>
                <div><span class="text-zinc-500">Telefon:</span> {{ $client->phone ?: '-' }}</div>
                <div><span class="text-zinc-500">Adresa:</span> {{ $client->address ?: '-' }}</div>
                <div><span class="text-zinc-500">Napomena:</span> {{ $client->note ?: '-' }}</div>
            </div>
        </flux:card>

        <flux:card class="space-y-3">
            <flux:heading size="lg">Pravni podaci</flux:heading>

            @if ($client->type?->key === 'person')
                <div class="grid gap-3 text-sm">
                    <div><span class="text-zinc-500">Ime:</span> {{ $client->person?->first_name ?: '-' }}</div>
                    <div><span class="text-zinc-500">Prezime:</span> {{ $client->person?->last_name ?: '-' }}</div>
                </div>
            @else
                <div class="grid gap-3 text-sm">
                    <div><span class="text-zinc-500">PIB:</span> {{ $client->company?->pib ?: '-' }}</div>
                    <div><span class="text-zinc-500">Matični broj:</span> {{ $client->company?->mb ?: '-' }}</div>
                    <div><span class="text-zinc-500">Račun:</span> {{ $client->company?->bank_account ?: '-' }}</div>
                </div>
            @endif
        </flux:card>
    </div>

    @if ($client->contacts->isNotEmpty())
        <flux:card class="space-y-4">
            <flux:heading size="lg">Kontakti</flux:heading>

            <x-ui.table>
                <x-ui.table.head>
                    <tr>
                        <x-ui.table.th>Ime i prezime</x-ui.table.th>
                        <x-ui.table.th>Pozicija</x-ui.table.th>
                        <x-ui.table.th>Kontakt</x-ui.table.th>
                        <x-ui.table.th>Glavni</x-ui.table.th>
                    </tr>
                </x-ui.table.head>
                <x-ui.table.body>
                    @foreach ($client->contacts as $contact)
                        <x-ui.table.row>
                            <x-ui.table.td class="font-medium">{{ $contact->full_name }}</x-ui.table.td>
                            <x-ui.table.td>{{ $contact->position ?: '-' }}</x-ui.table.td>
                            <x-ui.table.td>
                                <div>{{ $contact->email ?: '-' }}</div>
                                <div class="text-xs text-zinc-500">{{ $contact->phone ?: '-' }}</div>
                            </x-ui.table.td>
                            <x-ui.table.td>{{ $contact->is_primary ? 'Da' : 'Ne' }}</x-ui.table.td>
                        </x-ui.table.row>
                    @endforeach
                </x-ui.table.body>
            </x-ui.table>
        </flux:card>
    @endif

    <flux:card class="space-y-4">
        <flux:heading size="lg">Cenovnik usluga</flux:heading>

        <x-ui.table>
            <x-ui.table.head>
                <tr>
                    <x-ui.table.th>Usluga / projekat</x-ui.table.th>
                    <x-ui.table.th>Period</x-ui.table.th>
                    <x-ui.table.th>Cena</x-ui.table.th>
                    <x-ui.table.th>Status</x-ui.table.th>
                </tr>
            </x-ui.table.head>
            <x-ui.table.body>
                @forelse ($client->projectRates as $rate)
                    <x-ui.table.row>
                        <x-ui.table.td class="font-medium">{{ $rate->project?->name ?? '-' }}</x-ui.table.td>
                        <x-ui.table.td>{{ $rate->billingPeriod?->name ?? '-' }}</x-ui.table.td>
                        <x-ui.table.td>{{ number_format((float) $rate->price_amount, 2, ',', '.') }} {{ $rate->currency }}</x-ui.table.td>
                        <x-ui.table.td>{{ $rate->is_active ? 'Aktivan' : 'Neaktivan' }}</x-ui.table.td>
                    </x-ui.table.row>
                @empty
                    <x-ui.table.empty colspan="4">Nema definisanih usluga u cenovniku.</x-ui.table.empty>
                @endforelse
            </x-ui.table.body>
        </x-ui.table>
    </flux:card>
</div>
