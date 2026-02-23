<div class="flex h-full w-full flex-1 flex-col gap-6">
    @php($actionIconClass = 'size-3.5')

    <div class="flex flex-col gap-4 md:flex-row md:items-end md:justify-between">
        <div>
            <flux:heading size="xl">Issues</flux:heading>
            <flux:text>Tabelarni pregled sa filterima.</flux:text>
        </div>

        <div class="flex gap-2">
            <flux:button variant="ghost" :href="route('issues.board')" wire:navigate>Board</flux:button>
            <flux:button variant="primary" :href="route('issues.create')" wire:navigate>Novi issue</flux:button>
        </div>
    </div>

    <div class="grid gap-3 md:grid-cols-6">
        <flux:select wire:model.live="projectId" label="Projekat">
            <option value="">Svi</option>
            @foreach ($projects as $project)
                <option value="{{ $project->id }}">{{ $project->name }}</option>
            @endforeach
        </flux:select>
        <flux:input wire:model.live.debounce.300ms="search" label="Pretraga" placeholder="Naslov/opis" />
        <flux:select wire:model.live="categoryId" label="Kategorija">
            <option value="">Sve</option>
            @foreach ($categories as $category)
                <option value="{{ $category->id }}">{{ $category->name }}</option>
            @endforeach
        </flux:select>
        <flux:select wire:model.live="priorityId" label="Prioritet">
            <option value="">Svi</option>
            @foreach ($priorities as $priority)
                <option value="{{ $priority->id }}">{{ $priority->name }}</option>
            @endforeach
        </flux:select>
        <flux:select wire:model.live="clientId" label="Klijent">
            <option value="">Svi</option>
            @foreach ($clients as $client)
                <option value="{{ $client->id }}">{{ $client->display_name }}</option>
            @endforeach
        </flux:select>
        <flux:select wire:model.live="assigneeId" label="Dodeljeno">
            <option value="">Svi</option>
            @foreach ($assignees as $assignee)
                <option value="{{ $assignee->id }}">{{ $assignee->name }}</option>
            @endforeach
        </flux:select>
    </div>

    <x-ui.table>
        <x-ui.table.head>
            <tr>
                <x-ui.table.th>Naslov</x-ui.table.th>
                <x-ui.table.th>Status</x-ui.table.th>
                <x-ui.table.th>Prioritet</x-ui.table.th>
                <x-ui.table.th>Kategorija</x-ui.table.th>
                <x-ui.table.th>Rok</x-ui.table.th>
                <x-ui.table.th align="right">Akcija</x-ui.table.th>
            </tr>
        </x-ui.table.head>
        <x-ui.table.body>
            @forelse ($issues as $issue)
                <x-ui.table.row wire:key="issue-{{ $issue->id }}">
                    <x-ui.table.td>
                        <div class="font-medium">{{ $issue->title }}</div>
                        <div class="text-xs text-zinc-500">{{ $issue->project?->name }}</div>
                    </x-ui.table.td>
                    <x-ui.table.td>{{ $issue->status?->name }}</x-ui.table.td>
                    <x-ui.table.td>{{ $issue->priority?->name }}</x-ui.table.td>
                    <x-ui.table.td>{{ $issue->category?->name }}</x-ui.table.td>
                    <x-ui.table.td>{{ $issue->due_date?->format('d.m.Y') ?? '-' }}</x-ui.table.td>
                    <x-ui.table.td align="right">
                        <x-ui.table.actions>
                            <x-ui.buttons.icon-action :href="route('issues.show', $issue)" title="Otvori issue" color="primary" navigate>
                                <x-ui.icons.check :class="$actionIconClass" />
                            </x-ui.buttons.icon-action>
                            <x-ui.buttons.icon-action :href="route('issues.edit', $issue)" title="Izmeni issue" color="warning" navigate>
                                <x-ui.icons.pen :class="$actionIconClass" />
                            </x-ui.buttons.icon-action>
                        </x-ui.table.actions>
                    </x-ui.table.td>
                </x-ui.table.row>
            @empty
                <x-ui.table.empty colspan="6">Nema issue zapisa.</x-ui.table.empty>
            @endforelse
        </x-ui.table.body>
    </x-ui.table>

    <div>{{ $issues->links() }}</div>
</div>
