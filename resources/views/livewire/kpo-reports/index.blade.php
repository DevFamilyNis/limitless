<div class="flex h-full w-full flex-1 flex-col gap-6">
    @php($actionIconClass = 'size-3.5')

    <div class="flex flex-col gap-4 md:flex-row md:items-end md:justify-between">
        <div>
            <flux:heading size="xl">@lang('messages.kpo.title')</flux:heading>
            <flux:text>@lang('messages.kpo.subtitle')</flux:text>
        </div>

        <div class="w-full md:w-48">
            <flux:select wire:model.live="year" :label="__('messages.common.year')">
                @foreach ($years as $availableYear)
                    <option value="{{ $availableYear }}">{{ $availableYear }}</option>
                @endforeach
            </flux:select>
        </div>
    </div>

    @if (session('status'))
        <flux:callout variant="success" icon="check-circle">{{ session('status') }}</flux:callout>
    @endif

    <x-ui.table>
        <x-ui.table.head>
            <tr>
                <x-ui.table.th>@lang('messages.kpo.month')</x-ui.table.th>
                <x-ui.table.th>@lang('messages.common.status')</x-ui.table.th>
                <x-ui.table.th>@lang('messages.kpo.items')</x-ui.table.th>
                <x-ui.table.th>@lang('messages.kpo.services_total')</x-ui.table.th>
                <x-ui.table.th align="right">@lang('messages.common.action')</x-ui.table.th>
            </tr>
        </x-ui.table.head>
        <x-ui.table.body>
            @foreach ($months as $month)
                @php($report = $month['report'])
                <x-ui.table.row wire:key="kpo-month-{{ $month['month'] }}">
                    <x-ui.table.td class="font-medium">
                        {{ ucfirst($month['label']) }} {{ $year }}
                    </x-ui.table.td>
                    <x-ui.table.td>
                        @if (! $report)
                            <span class="inline-flex rounded-full bg-zinc-100 px-2 py-1 text-xs font-medium text-zinc-700 dark:bg-zinc-800 dark:text-zinc-300">
                                @lang('messages.kpo.status_not_generated')
                            </span>
                        @elseif ($month['is_locked'])
                            <span class="inline-flex rounded-full bg-emerald-100 px-2 py-1 text-xs font-medium text-emerald-700 dark:bg-emerald-900/40 dark:text-emerald-300">
                                @lang('messages.kpo.status_locked')
                            </span>
                        @else
                            <span class="inline-flex rounded-full bg-blue-100 px-2 py-1 text-xs font-medium text-blue-700 dark:bg-blue-900/40 dark:text-blue-300">
                                @lang('messages.kpo.status_generated')
                            </span>
                        @endif
                    </x-ui.table.td>
                    <x-ui.table.td>{{ $report?->rows_count ?? 0 }}</x-ui.table.td>
                    <x-ui.table.td>{{ number_format((float) ($report?->services_total ?? 0), 2, ',', '.') }}</x-ui.table.td>
                    <x-ui.table.td align="right">
                        <x-ui.table.actions>
                            @if (! $month['is_locked'])
                                <x-ui.buttons.icon-action
                                    wire:click="generateReport({{ $month['month'] }})"
                                    :title="$report ? __('messages.kpo.regenerate') : __('messages.kpo.generate')"
                                    color="primary"
                                >
                                    <x-ui.icons.pen :class="$actionIconClass" />
                                </x-ui.buttons.icon-action>
                            @endif

                            <x-ui.buttons.icon-action
                                wire:click="downloadPdf({{ $month['month'] }})"
                                :title="__('messages.actions.download_pdf')"
                                color="success"
                            >
                                <x-ui.icons.check :class="$actionIconClass" />
                            </x-ui.buttons.icon-action>

                            @if ($report && ! $month['is_locked'])
                                <x-ui.buttons.icon-action
                                    wire:click="lockReport({{ $report->id }})"
                                    :title="__('messages.kpo.lock')"
                                    color="danger"
                                >
                                    <x-ui.icons.disable :class="$actionIconClass" />
                                </x-ui.buttons.icon-action>
                            @endif
                        </x-ui.table.actions>
                    </x-ui.table.td>
                </x-ui.table.row>
            @endforeach
        </x-ui.table.body>
    </x-ui.table>
</div>
