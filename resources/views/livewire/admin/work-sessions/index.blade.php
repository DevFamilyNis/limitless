<div class="flex h-full w-full flex-1 flex-col gap-6">
    <div>
        <flux:heading size="xl">Radni dani</flux:heading>
        <flux:subheading>Pregled radnih sesija po korisniku i datumu.</flux:subheading>
    </div>

    {{-- Export panel --}}
    <div class="rounded-lg border border-zinc-200 bg-zinc-50 p-5 dark:border-zinc-700 dark:bg-zinc-800/50" x-data="{ open: false }">
        <button
            type="button"
            @click="open = !open"
            class="flex w-full items-center justify-between text-left"
        >
            <flux:heading size="lg">Izvezi PDF</flux:heading>
            <svg class="size-5 text-zinc-400 transition-transform" :class="{ 'rotate-180': open }" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="m19.5 8.25-7.5 7.5-7.5-7.5" />
            </svg>
        </button>

        <div x-show="open" x-collapse class="mt-4">
            <div class="flex flex-col gap-3 md:flex-row md:items-end">
                <flux:field class="w-full md:w-56">
                    <flux:label>Korisnik</flux:label>
                    <flux:select wire:model="reportUserId">
                        <option value="">Svi korisnici</option>
                        @foreach ($users as $user)
                            <option value="{{ $user->id }}">{{ $user->name }}</option>
                        @endforeach
                    </flux:select>
                </flux:field>

                <flux:field class="w-full md:w-44">
                    <flux:label>Od datuma</flux:label>
                    <flux:input wire:model="reportDateFrom" type="date" />
                    <flux:error name="reportDateFrom" />
                </flux:field>

                <flux:field class="w-full md:w-44">
                    <flux:label>Do datuma</flux:label>
                    <flux:input wire:model="reportDateTo" type="date" />
                    <flux:error name="reportDateTo" />
                </flux:field>

                <flux:button
                    variant="primary"
                    icon="arrow-down-tray"
                    wire:click="downloadReport"
                    wire:loading.attr="disabled"
                    class="shrink-0"
                >
                    Preuzmi PDF
                </flux:button>
            </div>
        </div>
    </div>

    {{-- Table filters --}}
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
                @role('super-admin')
                    <x-ui.table.th></x-ui.table.th>
                @endrole
            </tr>
        </x-ui.table.head>
        <x-ui.table.body>
            @forelse ($sessions as $session)
                <x-ui.table.row>
                    <x-ui.table.td>
                        @role('super-admin')
                            <button
                                type="button"
                                wire:click="openUserSettings({{ $session->user->id }})"
                                class="underline decoration-dotted hover:text-zinc-900 dark:hover:text-zinc-100"
                            >
                                {{ $session->user->name }}
                            </button>
                        @else
                            {{ $session->user->name }}
                        @endrole
                    </x-ui.table.td>
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
                    @role('super-admin')
                        <x-ui.table.td>
                            <div class="flex items-center gap-2">
                                @if ($session->ended_at === null)
                                    <flux:button
                                        size="sm"
                                        variant="filled"
                                        wire:click="confirmFinish({{ $session->id }})"
                                        wire:loading.attr="disabled"
                                    >
                                        Završi
                                    </flux:button>
                                @endif
                                <flux:button
                                    size="sm"
                                    variant="danger"
                                    wire:click="confirmDelete({{ $session->id }})"
                                    wire:loading.attr="disabled"
                                >
                                    Obriši
                                </flux:button>
                            </div>
                        </x-ui.table.td>
                    @endrole
                </x-ui.table.row>
            @empty
                <x-ui.table.row>
                    <x-ui.table.td colspan="7" class="text-center">Nema pronađenih sesija.</x-ui.table.td>
                </x-ui.table.row>
            @endforelse
        </x-ui.table.body>
    </x-ui.table>

    <div>{{ $sessions->links() }}</div>

    <flux:modal name="confirm-finish-session" wire:model="showFinishConfirm" class="max-w-md">
        <div class="space-y-5">
            <div>
                <flux:heading size="lg">Završiti sesiju?</flux:heading>
                <flux:subheading>Sesija će biti označena kao završena sa trenutnim vremenom. Ova akcija se ne može poništiti.</flux:subheading>
            </div>
            <div class="flex justify-end gap-2">
                <flux:modal.close>
                    <flux:button variant="filled">Otkaži</flux:button>
                </flux:modal.close>
                <flux:button variant="primary" wire:click="forceFinish" wire:loading.attr="disabled">
                    Završi sesiju
                </flux:button>
            </div>
        </div>
    </flux:modal>

    <flux:modal name="confirm-delete-session" wire:model="showDeleteConfirm" class="max-w-md">
        <div class="space-y-5">
            <div>
                <flux:heading size="lg">Obrisati sesiju?</flux:heading>
                <flux:subheading>Sesija će biti trajno obrisana. Ova akcija je nepovratna.</flux:subheading>
            </div>
            <div class="flex justify-end gap-2">
                <flux:modal.close>
                    <flux:button variant="filled">Otkaži</flux:button>
                </flux:modal.close>
                <flux:button variant="danger" wire:click="delete" wire:loading.attr="disabled">
                    Obriši sesiju
                </flux:button>
            </div>
        </div>
    </flux:modal>

    <flux:modal name="work-session-user-settings" wire:model="showUserSettingsModal" class="max-w-md">
        <form wire:submit.prevent="saveUserSettings" class="space-y-5" x-data="{ enabled: @entangle('userSettingsReminderEnabled') }">
            <div>
                <flux:heading size="lg">Podsetnik za korisnika</flux:heading>
                <flux:subheading>{{ $userSettingsUserName }}</flux:subheading>
            </div>

            <flux:field variant="inline">
                <flux:switch x-model="enabled" />
                <flux:label>Podsetnik uključen</flux:label>
                <flux:error name="userSettingsReminderEnabled" />
            </flux:field>

            <div x-show="enabled" x-collapse>
                <flux:field>
                    <flux:label>Kašnjenje podsetnika (minuti)</flux:label>
                    <flux:description>Koliko minuta nakon početka radnog dana da se prikaže podsetnik ovom korisniku.</flux:description>
                    <flux:input wire:model="userSettingsReminderDelayMinutes" type="number" min="15" max="480" />
                    <flux:error name="userSettingsReminderDelayMinutes" />
                </flux:field>
            </div>

            <div class="flex justify-end gap-2">
                <flux:modal.close>
                    <flux:button variant="filled">Otkaži</flux:button>
                </flux:modal.close>
                <flux:button type="submit" variant="primary" wire:loading.attr="disabled">
                    Sačuvaj
                </flux:button>
            </div>
        </form>
    </flux:modal>
</div>
