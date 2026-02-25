<div class="flex h-full w-full flex-1 flex-col gap-6">
    @php($icon = 'size-3.5')
    <div class="flex items-end justify-between">
        <div><flux:heading size="xl">@lang('messages.menu.catIssues')</flux:heading><flux:text>@lang('messages.settings.issue_categories.subtitle')</flux:text></div>
        <flux:button variant="primary" :href="route('settings.issue-categories.create')" wire:navigate>@lang('messages.actions.add')</flux:button>
    </div>
    <x-ui.table>
        <x-ui.table.head><tr><x-ui.table.th>@lang('messages.table.name')</x-ui.table.th><x-ui.table.th>@lang('messages.status_labels.active_f')</x-ui.table.th><x-ui.table.th align="right">@lang('messages.common.action')</x-ui.table.th></tr></x-ui.table.head>
        <x-ui.table.body>
            @foreach ($categories as $category)
                @php($categoryColor = \App\Support\IssueLabelPalette::forCategory($category->name))
                <x-ui.table.row>
                    <x-ui.table.td>
                        <span
                            class="inline-flex rounded-full border px-2 py-1 text-xs font-medium"
                            style="background-color: {{ $categoryColor['soft_bg'] }}; border-color: {{ $categoryColor['border'] }}; border-width: {{ $categoryColor['border_width'] }}; color: {{ $categoryColor['hex'] }}; font-weight: {{ $categoryColor['font_weight'] }};"
                        >
                            {{ $category->name }}
                        </span>
                    </x-ui.table.td>
                    <x-ui.table.td>{{ $category->is_active ? __('messages.common.yes') : __('messages.common.no') }}</x-ui.table.td>
                    <x-ui.table.td align="right">
                        <x-ui.table.actions>
                            <x-ui.buttons.icon-action :href="route('settings.issue-categories.edit', $category)" color="primary" navigate><x-ui.icons.pen :class="$icon" /></x-ui.buttons.icon-action>
                            <x-ui.buttons.icon-action wire:click="deleteCategory({{ $category->id }})" color="danger"><x-ui.icons.trash :class="$icon" /></x-ui.buttons.icon-action>
                        </x-ui.table.actions>
                    </x-ui.table.td>
                </x-ui.table.row>
            @endforeach
        </x-ui.table.body>
    </x-ui.table>
</div>
