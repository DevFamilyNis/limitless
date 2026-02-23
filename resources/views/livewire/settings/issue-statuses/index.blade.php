<div class="flex h-full w-full flex-1 flex-col gap-6">
    @php($icon = 'size-3.5')
    <div class="flex items-end justify-between">
        <div>
            <flux:heading size="xl">Statusi</flux:heading>
            <flux:text>Mini CRUD za board kolone.</flux:text>
        </div>
        <flux:button variant="primary" :href="route('settings.issue-statuses.create')" wire:navigate>Dodaj</flux:button>
    </div>

    <x-ui.table>
        <x-ui.table.head><tr><x-ui.table.th>Key</x-ui.table.th><x-ui.table.th>Naziv</x-ui.table.th><x-ui.table.th>Sort</x-ui.table.th><x-ui.table.th>Aktivan</x-ui.table.th><x-ui.table.th align="right">Akcija</x-ui.table.th></tr></x-ui.table.head>
        <x-ui.table.body>
            @foreach ($statuses as $status)
                <x-ui.table.row>
                    <x-ui.table.td>{{ $status->key }}</x-ui.table.td>
                    <x-ui.table.td>{{ $status->name }}</x-ui.table.td>
                    <x-ui.table.td>{{ $status->sort_order }}</x-ui.table.td>
                    <x-ui.table.td>{{ $status->is_active ? 'Da' : 'Ne' }}</x-ui.table.td>
                    <x-ui.table.td align="right">
                        <x-ui.table.actions>
                            <x-ui.buttons.icon-action :href="route('settings.issue-statuses.edit', $status)" color="primary" navigate><x-ui.icons.pen :class="$icon" /></x-ui.buttons.icon-action>
                            <x-ui.buttons.icon-action wire:click="deleteStatus({{ $status->id }})" color="danger"><x-ui.icons.trash :class="$icon" /></x-ui.buttons.icon-action>
                        </x-ui.table.actions>
                    </x-ui.table.td>
                </x-ui.table.row>
            @endforeach
        </x-ui.table.body>
    </x-ui.table>
</div>
