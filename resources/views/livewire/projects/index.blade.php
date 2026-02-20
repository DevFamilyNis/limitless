<div class="flex h-full w-full flex-1 flex-col gap-6">
    @php($actionIconClass = 'size-4')

    <div class="flex flex-col gap-4 md:flex-row md:items-end md:justify-between">
        <div>
            <flux:heading size="xl">Projekti</flux:heading>
            <flux:text>Pregled, pretraga i upravljanje projektima.</flux:text>
        </div>

        <flux:button variant="primary" :href="route('projects.create')" wire:navigate>
            Dodaj
        </flux:button>
    </div>

    @if (session('status'))
        <flux:callout variant="success" icon="check-circle">{{ session('status') }}</flux:callout>
    @endif

    <div class="grid gap-3 md:grid-cols-3">
        <flux:input wire:model.live.debounce.300ms="search" label="Pretraga" placeholder="Kod, naziv ili opis" />

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
                    <th class="px-4 py-3 text-left">Kod</th>
                    <th class="px-4 py-3 text-left">Naziv</th>
                    <th class="px-4 py-3 text-left">Opis</th>
                    <th class="px-4 py-3 text-left">Status</th>
                    <th class="px-4 py-3 text-right">Akcija</th>
                </tr>
            </thead>
            <tbody>
                @forelse ($projects as $project)
                    <tr wire:key="project-{{ $project->id }}" class="border-t border-zinc-200 dark:border-zinc-700">
                        <td class="px-4 py-3 font-medium">{{ $project->code }}</td>
                        <td class="px-4 py-3">{{ $project->name }}</td>
                        <td class="px-4 py-3">{{ $project->description ?: '-' }}</td>
                        <td class="px-4 py-3">
                            @if ($project->is_active)
                                <span class="inline-flex rounded-full bg-green-100 px-2 py-1 text-xs font-medium text-green-700 dark:bg-green-900/40 dark:text-green-300">Aktivan</span>
                            @else
                                <span class="inline-flex rounded-full bg-zinc-100 px-2 py-1 text-xs font-medium text-zinc-700 dark:bg-zinc-800 dark:text-zinc-300">Neaktivan</span>
                            @endif
                        </td>
                        <td class="px-4 py-3">
                            <div class="flex items-center justify-end gap-2">
                                <flux:button
                                    size="sm"
                                    variant="filled"
                                    class="size-9 p-0"
                                    :href="route('projects.edit', $project)"
                                    wire:navigate
                                    title="Izmeni projekat"
                                >
                                    <x-ui.icons.pen :class="$actionIconClass" />
                                </flux:button>

                                <flux:button
                                    size="sm"
                                    variant="filled"
                                    class="size-9 p-0"
                                    wire:click="toggleActive({{ $project->id }})"
                                    :title="$project->is_active ? 'Deaktiviraj projekat' : 'Aktiviraj projekat'"
                                >
                                    <x-ui.icons.disable :class="$actionIconClass" />
                                </flux:button>

                                <flux:button
                                    size="sm"
                                    variant="danger"
                                    class="size-9 p-0"
                                    wire:click="deleteProject({{ $project->id }})"
                                    title="Obriši projekat"
                                >
                                    <x-ui.icons.trash :class="$actionIconClass" />
                                </flux:button>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td class="px-4 py-6 text-center text-zinc-500" colspan="5">
                            Nema projekata za prikaz.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div>
        {{ $projects->links() }}
    </div>
</div>
