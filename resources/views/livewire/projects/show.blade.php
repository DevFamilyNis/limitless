<div class="mx-auto flex w-full max-w-6xl flex-col gap-6">
    <div class="flex flex-col gap-4 md:flex-row md:items-start md:justify-between">
        <div>
            <flux:heading size="xl">{{ $project->name }}</flux:heading>
            <flux:text>Kod: {{ $project->code }} | Detalji projekta, korisnici i zbir mesečnih faktura.</flux:text>
        </div>

        <div class="flex items-center gap-2">
            <flux:button variant="ghost" :href="route('projects.index')" wire:navigate>
                Nazad
            </flux:button>
            <flux:button variant="primary" :href="route('projects.edit', $project)" wire:navigate>
                Izmeni
            </flux:button>
        </div>
    </div>

    <div class="grid gap-4 md:grid-cols-3">
        <flux:card>
            <flux:text class="text-xs text-zinc-500">Status</flux:text>
            <flux:heading size="lg">{{ $project->is_active ? 'Aktivan' : 'Neaktivan' }}</flux:heading>
        </flux:card>

        <flux:card>
            <flux:text class="text-xs text-zinc-500">Vlasnik</flux:text>
            <flux:heading size="lg">{{ $project->user?->name ?? '-' }}</flux:heading>
        </flux:card>

        <flux:card>
            <flux:text class="text-xs text-zinc-500">Zbir faktura (tekući mesec)</flux:text>
            <flux:heading size="lg">{{ number_format($currentMonthInvoiceTotal, 2, ',', '.') }} RSD</flux:heading>
        </flux:card>
    </div>

    <flux:card class="space-y-3">
        <flux:heading size="lg">Opis</flux:heading>
        <flux:text>{{ $project->description ?: '-' }}</flux:text>
    </flux:card>

    <div class="grid gap-6 lg:grid-cols-2">
        <flux:card class="space-y-4">
            <flux:heading size="lg">Korisnici projekta</flux:heading>

            @if ($clients->isEmpty())
                <flux:text class="text-zinc-500">Nema povezanih korisnika/klijenata preko cenovnika.</flux:text>
            @else
                <x-ui.table>
                    <x-ui.table.head>
                        <tr>
                            <x-ui.table.th>Naziv</x-ui.table.th>
                            <x-ui.table.th>Tip</x-ui.table.th>
                            <x-ui.table.th>Kontakt</x-ui.table.th>
                        </tr>
                    </x-ui.table.head>
                    <x-ui.table.body>
                        @foreach ($clients as $client)
                            <x-ui.table.row>
                                <x-ui.table.td class="font-medium">
                                    @if ($client->type?->key === 'person' && $client->person)
                                        {{ trim($client->person->first_name.' '.$client->person->last_name) }}
                                    @else
                                        {{ $client->display_name }}
                                    @endif
                                </x-ui.table.td>
                                <x-ui.table.td>{{ $client->type?->name ?? '-' }}</x-ui.table.td>
                                <x-ui.table.td>
                                    <div>{{ $client->email ?: '-' }}</div>
                                    <div class="text-xs text-zinc-500">{{ $client->phone ?: '-' }}</div>
                                </x-ui.table.td>
                            </x-ui.table.row>
                        @endforeach
                    </x-ui.table.body>
                </x-ui.table>
            @endif
        </flux:card>

        <flux:card class="space-y-4">
            <flux:heading size="lg">Zbir mesečnih faktura (6 meseci)</flux:heading>

            <x-ui.table>
                <x-ui.table.head>
                    <tr>
                        <x-ui.table.th>Mesec</x-ui.table.th>
                        <x-ui.table.th>Iznos (RSD)</x-ui.table.th>
                    </tr>
                </x-ui.table.head>
                <x-ui.table.body>
                    @foreach ($monthlyTotals as $row)
                        <x-ui.table.row>
                            <x-ui.table.td>{{ $row['month'] }}</x-ui.table.td>
                            <x-ui.table.td>{{ number_format($row['total'], 2, ',', '.') }}</x-ui.table.td>
                        </x-ui.table.row>
                    @endforeach
                </x-ui.table.body>
            </x-ui.table>
        </flux:card>
    </div>
</div>
