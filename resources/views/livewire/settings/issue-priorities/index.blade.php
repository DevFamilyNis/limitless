<div class="flex h-full w-full flex-1 flex-col gap-6">
    @php($icon = 'size-3.5')
    <div class="flex items-end justify-between">
        <div><flux:heading size="xl">@lang('messages.menu.priorities')</flux:heading><flux:text>@lang('messages.settings.issue_priorities.subtitle')</flux:text></div>
        <flux:button variant="primary" :href="route('settings.issue-priorities.create')" wire:navigate>@lang('messages.actions.add')</flux:button>
    </div>
    <x-ui.table>
        <x-ui.table.head><tr><x-ui.table.th>@lang('messages.common.key')</x-ui.table.th><x-ui.table.th>@lang('messages.table.name')</x-ui.table.th><x-ui.table.th>@lang('messages.common.sort')</x-ui.table.th><x-ui.table.th align="right">@lang('messages.common.action')</x-ui.table.th></tr></x-ui.table.head>
        <x-ui.table.body>
            @foreach ($priorities as $priority)
                @php($priorityColor = \App\Support\IssueLabelPalette::forPriority($priority->key, $priority->name))
                <x-ui.table.row>
                    <x-ui.table.td>{{ $priority->key }}</x-ui.table.td>
                    <x-ui.table.td>
                        <span
                            class="inline-flex rounded-full border px-2 py-1 text-xs font-medium"
                            style="background-color: {{ $priorityColor['soft_bg'] }}; border-color: {{ $priorityColor['border'] }}; border-width: {{ $priorityColor['border_width'] }}; color: {{ $priorityColor['hex'] }}; font-weight: {{ $priorityColor['font_weight'] }};"
                        >
                            {{ $priority->name }}
                        </span>
                    </x-ui.table.td>
                    <x-ui.table.td>{{ $priority->sort_order }}</x-ui.table.td>
                    <x-ui.table.td align="right">
                        <x-ui.table.actions>
                            <x-ui.buttons.icon-action :href="route('settings.issue-priorities.edit', $priority)" color="primary" navigate><x-ui.icons.pen :class="$icon" /></x-ui.buttons.icon-action>
                            <x-ui.buttons.icon-action wire:click="deletePriority({{ $priority->id }})" color="danger"><x-ui.icons.trash :class="$icon" /></x-ui.buttons.icon-action>
                        </x-ui.table.actions>
                    </x-ui.table.td>
                </x-ui.table.row>
            @endforeach
        </x-ui.table.body>
    </x-ui.table>
</div>
