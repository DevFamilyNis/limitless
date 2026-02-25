<div class="flex h-full w-full flex-1 flex-col gap-6">
    @php($icon = 'size-3.5')
    <div class="flex items-end justify-between">
        <div>
            <flux:heading size="xl">@lang('messages.menu.statuses')</flux:heading>
            <flux:text>@lang('messages.settings.issue_statuses.subtitle')</flux:text>
        </div>
        <flux:button variant="primary" :href="route('settings.issue-statuses.create')" wire:navigate>@lang('messages.actions.add')</flux:button>
    </div>

    <x-ui.table>
        <x-ui.table.head><tr><x-ui.table.th>@lang('messages.common.key')</x-ui.table.th><x-ui.table.th>@lang('messages.table.name')</x-ui.table.th><x-ui.table.th>@lang('messages.common.sort')</x-ui.table.th><x-ui.table.th>@lang('messages.common.active')</x-ui.table.th><x-ui.table.th align="right">@lang('messages.common.action')</x-ui.table.th></tr></x-ui.table.head>
        <x-ui.table.body>
            @foreach ($statuses as $status)
                @php($statusColor = \App\Support\IssueLabelPalette::forStatus($status->key, $status->name))
                <x-ui.table.row>
                    <x-ui.table.td>{{ $status->key }}</x-ui.table.td>
                    <x-ui.table.td>
                        <span
                            class="inline-flex rounded-full border px-2 py-1 text-xs font-medium"
                            style="background-color: {{ $statusColor['soft_bg'] }}; border-color: {{ $statusColor['border'] }}; border-width: {{ $statusColor['border_width'] }}; color: {{ $statusColor['hex'] }}; font-weight: {{ $statusColor['font_weight'] }};"
                        >
                            {{ $status->name }}
                        </span>
                    </x-ui.table.td>
                    <x-ui.table.td>{{ $status->sort_order }}</x-ui.table.td>
                    <x-ui.table.td>{{ $status->is_active ? __('messages.common.yes') : __('messages.common.no') }}</x-ui.table.td>
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
