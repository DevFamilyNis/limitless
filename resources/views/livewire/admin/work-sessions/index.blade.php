<div class="flex h-full w-full flex-1 flex-col gap-6">
    <div>
        <flux:heading size="xl">Radni dani</flux:heading>
        <flux:subheading>Pregled radnih sesija po korisniku i datumu.</flux:subheading>
    </div>

    <div class="flex flex-col gap-3 md:flex-row md:items-end">
        <flux:field class="w-full md:w-56">
            <flux:label>Korisnik</flux:label>
            <flux:select wire:model.live="selectedUserId">
                <option value="">Svi korisnici</option>
                @foreach ($users as $user)
                    <option value="{{ $user->id }}">{{ $user->name }}</option>
                @endforeach
            </flux:select>
        </flux:field>

        <flux:field class="w-full md:w-44">
            <flux:label>Datum</flux:label>
            <flux:input wire:model.live="selectedDate" type="date" />
        </flux:field>
    </div>

    <x-ui.table>
        <x-ui.table.head>
            <tr>
                <x-ui.table.th>Korisnik</x-ui.table.th>
                <x-ui.table.th>Datum</x-ui.table.th>
                <x-ui.table.th>Početak</x-ui.table.th>
                <x-ui.table.th>Kraj</x-ui.table.th>
                <x-ui.table.th>Trajanje</x-ui.table.th>
                <x-ui.table.th>Podsetnik</x-ui.table.th>
            </tr>
        </x-ui.table.head>
        <x-ui.table.body>
            @forelse ($sessions as $session)
                <x-ui.table.row>
                    <x-ui.table.td>{{ $session->user->name }}</x-ui.table.td>
                    <x-ui.table.td>{{ $session->work_date->format('d.m.Y') }}</x-ui.table.td>
                    <x-ui.table.td>{{ $session->started_at->format('H:i') }}</x-ui.table.td>
                    <x-ui.table.td>
                        @if ($session->ended_at)
                            {{ $session->ended_at->format('H:i') }}
                        @else
                            <flux:badge color="lime" size="sm">Aktivan</flux:badge>
                        @endif
                    </x-ui.table.td>
                    <x-ui.table.td>
                        @if ($session->duration_minutes !== null)
                            {{ intdiv($session->duration_minutes, 60) }}h {{ $session->duration_minutes % 60 }}m
                        @else
                            —
                        @endif
                    </x-ui.table.td>
                    <x-ui.table.td>
                        @if ($session->reminder_acknowledged_at)
                            <flux:badge color="zinc" size="sm">Potvrđen</flux:badge>
                        @elseif ($session->reminder_due_at)
                            @if ($session->reminder_due_at->isPast())
                                <flux:badge color="red" size="sm">Čeka odgovor</flux:badge>
                            @else
                                <flux:badge color="blue" size="sm">{{ $session->reminder_due_at->format('H:i') }}</flux:badge>
                            @endif
                        @else
                            —
                        @endif
                    </x-ui.table.td>
                </x-ui.table.row>
            @empty
                <x-ui.table.row>
                    <x-ui.table.td colspan="6" class="text-center">Nema pronađenih sesija.</x-ui.table.td>
                </x-ui.table.row>
            @endforelse
        </x-ui.table.body>
    </x-ui.table>

    <div>{{ $sessions->links() }}</div>
</div>
