<div class="flex h-full w-full flex-1 flex-col gap-6">
    @php($actionIconClass = 'size-3.5')

    <div class="flex flex-col gap-4 md:flex-row md:items-end md:justify-between">
        <div>
            <flux:heading size="xl">@lang('messages.tax_years.title')</flux:heading>
            <flux:text>@lang('messages.tax_years.subtitle')</flux:text>
        </div>

        <flux:button variant="primary" :href="route('tax-years.create')" wire:navigate>
            @lang('messages.actions.add')
        </flux:button>
    </div>

    @if (session('status'))
        <flux:callout variant="success" icon="check-circle">{{ session('status') }}</flux:callout>
    @endif

    <div class="grid gap-3 md:grid-cols-3">
        <flux:input wire:model.live.debounce.300ms="search" :label="__('messages.common.search')" :placeholder="__('messages.tax_years.search_placeholder')" />
    </div>

    <x-ui.table>
        <x-ui.table.head>
            <tr>
                <x-ui.table.th>@lang('messages.common.year')</x-ui.table.th>
                <x-ui.table.th>@lang('messages.tax_years.first_threshold')</x-ui.table.th>
                <x-ui.table.th>@lang('messages.tax_years.second_threshold')</x-ui.table.th>
                <x-ui.table.th align="right">@lang('messages.common.action')</x-ui.table.th>
            </tr>
        </x-ui.table.head>
        <x-ui.table.body>
            @forelse ($taxYears as $taxYear)
                <x-ui.table.row wire:key="tax-year-{{ $taxYear->id }}">
                    <x-ui.table.td class="font-medium">{{ $taxYear->year }}</x-ui.table.td>
                    <x-ui.table.td>{{ number_format((float) $taxYear->first_threshold_amount, 2, ',', '.') }}</x-ui.table.td>
                    <x-ui.table.td>{{ number_format((float) $taxYear->second_threshold_amount, 2, ',', '.') }}</x-ui.table.td>
                    <x-ui.table.td align="right">
                        <x-ui.table.actions>
                            <x-ui.buttons.icon-action :href="route('tax-years.edit', $taxYear)" :title="__('messages.tax_years.edit_action')" color="primary" navigate>
                                <x-ui.icons.pen :class="$actionIconClass" />
                            </x-ui.buttons.icon-action>
                            <x-ui.buttons.icon-action wire:click="deleteTaxYear({{ $taxYear->id }})" :title="__('messages.tax_years.delete_action')" color="danger">
                                <x-ui.icons.trash :class="$actionIconClass" />
                            </x-ui.buttons.icon-action>
                        </x-ui.table.actions>
                    </x-ui.table.td>
                </x-ui.table.row>
            @empty
                <x-ui.table.empty colspan="4">
                    @lang('messages.tax_years.empty')
                </x-ui.table.empty>
            @endforelse
        </x-ui.table.body>
    </x-ui.table>

    <div>
        {{ $taxYears->links() }}
    </div>
</div>
