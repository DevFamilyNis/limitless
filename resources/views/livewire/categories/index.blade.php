<div class="flex h-full w-full flex-1 flex-col gap-6">
    @php($actionIconClass = 'size-3.5')

    <div class="flex flex-col gap-4 md:flex-row md:items-end md:justify-between">
        <div>
            <flux:heading size="xl">Kategorije</flux:heading>
            <flux:text>Upravljanje kategorijama prihoda i troškova.</flux:text>
        </div>

        <flux:button variant="primary" :href="route('categories.create')" wire:navigate>
            Dodaj
        </flux:button>
    </div>

    @if (session('status'))
        <flux:callout variant="success" icon="check-circle">{{ session('status') }}</flux:callout>
    @endif

    <div class="grid gap-3 md:grid-cols-3">
        <flux:input wire:model.live.debounce.300ms="search" label="Pretraga" placeholder="Naziv kategorije" />
        <flux:select wire:model.live="typeFilter" label="Tip">
            <option value="all">Svi</option>
            @foreach ($types as $type)
                <option value="{{ $type->key }}">{{ $type->key === 'expense' ? 'Rashod' : $type->name }}</option>
            @endforeach
        </flux:select>
    </div>

    <x-ui.table>
        <x-ui.table.head>
            <tr>
                <x-ui.table.th>Naziv</x-ui.table.th>
                <x-ui.table.th>Tip</x-ui.table.th>
                <x-ui.table.th align="right">Akcija</x-ui.table.th>
            </tr>
        </x-ui.table.head>
        <x-ui.table.body>
            @forelse ($categories as $category)
                <x-ui.table.row wire:key="category-{{ $category->id }}">
                    <x-ui.table.td class="font-medium">{{ $category->name }}</x-ui.table.td>
                    <x-ui.table.td>{{ $category->type?->key === 'expense' ? 'Rashod' : $category->type?->name }}</x-ui.table.td>
                    <x-ui.table.td align="right">
                        <x-ui.table.actions>
                            <x-ui.buttons.icon-action :href="route('categories.edit', $category)" title="Izmeni kategoriju" color="primary" navigate>
                                <x-ui.icons.pen :class="$actionIconClass" />
                            </x-ui.buttons.icon-action>
                            <x-ui.buttons.icon-action wire:click="deleteCategory({{ $category->id }})" title="Obriši kategoriju" color="danger">
                                <x-ui.icons.trash :class="$actionIconClass" />
                            </x-ui.buttons.icon-action>
                        </x-ui.table.actions>
                    </x-ui.table.td>
                </x-ui.table.row>
            @empty
                <x-ui.table.empty colspan="3">
                    Nema unetih kategorija.
                </x-ui.table.empty>
            @endforelse
        </x-ui.table.body>
    </x-ui.table>

    <div>
        {{ $categories->links() }}
    </div>
</div>
