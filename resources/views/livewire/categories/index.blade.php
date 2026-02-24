<div class="flex h-full w-full flex-1 flex-col gap-6">
    @php($actionIconClass = 'size-3.5')

    <div class="flex flex-col gap-4 md:flex-row md:items-end md:justify-between">
        <div>
            <flux:heading size="xl">@lang('messages.categories.title')</flux:heading>
            <flux:text>@lang('messages.categories.subtitle')</flux:text>
        </div>

        <flux:button variant="primary" :href="route('categories.create')" wire:navigate>
            @lang('messages.actions.add')
        </flux:button>
    </div>

    @if (session('status'))
        <flux:callout variant="success" icon="check-circle">{{ session('status') }}</flux:callout>
    @endif

    <div class="grid gap-3 md:grid-cols-3">
        <flux:input wire:model.live.debounce.300ms="search" :label="__('messages.common.search')" :placeholder="__('messages.categories.search_placeholder')" />
        <flux:select wire:model.live="typeFilter" :label="__('messages.common.type')">
            <option value="all">@lang('messages.common.all')</option>
            @foreach ($types as $type)
                <option value="{{ $type->key }}">{{ $type->key === 'expense' ? __('messages.categories.expense') : __('messages.categories.income') }}</option>
            @endforeach
        </flux:select>
    </div>

    <x-ui.table>
        <x-ui.table.head>
            <tr>
                <x-ui.table.th>@lang('messages.table.name')</x-ui.table.th>
                <x-ui.table.th>@lang('messages.common.type')</x-ui.table.th>
                <x-ui.table.th align="right">@lang('messages.common.action')</x-ui.table.th>
            </tr>
        </x-ui.table.head>
        <x-ui.table.body>
            @forelse ($categories as $category)
                <x-ui.table.row wire:key="category-{{ $category->id }}">
                    <x-ui.table.td class="font-medium">{{ $category->name }}</x-ui.table.td>
                    <x-ui.table.td>{{ $category->type?->key === 'expense' ? __('messages.categories.expense') : __('messages.categories.income') }}</x-ui.table.td>
                    <x-ui.table.td align="right">
                        <x-ui.table.actions>
                            <x-ui.buttons.icon-action :href="route('categories.edit', $category)" :title="__('messages.categories.edit_action')" color="primary" navigate>
                                <x-ui.icons.pen :class="$actionIconClass" />
                            </x-ui.buttons.icon-action>
                            <x-ui.buttons.icon-action wire:click="deleteCategory({{ $category->id }})" :title="__('messages.categories.delete_action')" color="danger">
                                <x-ui.icons.trash :class="$actionIconClass" />
                            </x-ui.buttons.icon-action>
                        </x-ui.table.actions>
                    </x-ui.table.td>
                </x-ui.table.row>
            @empty
                <x-ui.table.empty colspan="3">
                    @lang('messages.categories.empty')
                </x-ui.table.empty>
            @endforelse
        </x-ui.table.body>
    </x-ui.table>

    <div>
        {{ $categories->links() }}
    </div>
</div>
