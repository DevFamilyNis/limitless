<div class="flex h-full w-full flex-1 flex-col gap-6">
    @php($actionIconClass = 'size-3.5')

    <div class="flex flex-col gap-4 md:flex-row md:items-end md:justify-between">
        <div>
            <flux:heading size="xl">@lang('messages.monthly_expenses.title')</flux:heading>
            <flux:text>@lang('messages.monthly_expenses.subtitle')</flux:text>
        </div>
    </div>

    @if (session('status'))
        <flux:callout variant="success" icon="check-circle">{{ session('status') }}</flux:callout>
    @endif

    <div class="grid gap-3 md:grid-cols-1">
        <flux:input wire:model.live.debounce.300ms="search" :label="__('messages.common.search')" :placeholder="__('messages.monthly_expenses.search_placeholder')" />
    </div>

    <div class="rounded-xl border border-zinc-200 p-4 dark:border-zinc-700">
        <div class="mb-4 flex items-center justify-between gap-3">
            <flux:heading size="sm">
                {{ $editingItemId ? __('messages.monthly_expenses.edit_title') : __('messages.monthly_expenses.new_title') }}
            </flux:heading>
            @if ($editingItemId)
                <flux:button variant="ghost" wire:click="cancelEditing">@lang('messages.actions.back')</flux:button>
            @endif
        </div>

        @if ($billingPeriods->isEmpty())
            <flux:callout variant="warning" icon="exclamation-triangle">
                @lang('messages.monthly_expenses.requirements')
            </flux:callout>
        @else
            <form wire:submit="saveItem" class="grid gap-3 md:grid-cols-2">
                <flux:select wire:model="billingPeriodId" :label="__('messages.client_project_rates.billing_period')" required>
                    @foreach ($billingPeriods as $period)
                        <option value="{{ $period->id }}">{{ $period->name }}</option>
                    @endforeach
                </flux:select>
                <flux:input wire:model="title" :label="__('messages.common.title')" required />
                <flux:input wire:model="amount" :label="__('messages.common.amount')" type="number" step="0.01" min="0.01" required />
                <div class="md:col-span-2">
                    <flux:textarea wire:model="note" :label="__('messages.form.note')" rows="3" />
                </div>
                <div class="md:col-span-2">
                    <flux:button type="submit" variant="primary">@lang('messages.actions.save')</flux:button>
                </div>
            </form>
        @endif
    </div>

    <x-ui.table>
        <x-ui.table.head>
            <tr>
                <x-ui.table.th>@lang('messages.common.title')</x-ui.table.th>
                <x-ui.table.th>@lang('messages.client_project_rates.billing_period')</x-ui.table.th>
                <x-ui.table.th>@lang('messages.form.note')</x-ui.table.th>
                <x-ui.table.th align="right">@lang('messages.monthly_expenses.amount_for_period')</x-ui.table.th>
                <x-ui.table.th align="right">@lang('messages.common.amount')</x-ui.table.th>
                <x-ui.table.th align="right">@lang('messages.common.action')</x-ui.table.th>
            </tr>
        </x-ui.table.head>
        <x-ui.table.body>
            @forelse ($items as $item)
                <x-ui.table.row wire:key="monthly-expense-item-{{ $item->id }}">
                    <x-ui.table.td class="font-medium">{{ $item->title }}</x-ui.table.td>
                    <x-ui.table.td>{{ $item->billingPeriod?->name }}</x-ui.table.td>
                    <x-ui.table.td>{{ $item->note ?: '-' }}</x-ui.table.td>
                    <x-ui.table.td align="right">{{ number_format((float) $item->amount, 2, ',', '.') }} RSD</x-ui.table.td>
                    <x-ui.table.td align="right">
                        @if ($item->billingPeriod?->key === 'yearly')
                            {{ number_format((float) $item->amount / 12, 2, ',', '.') }} RSD
                        @else
                            {{ number_format((float) $item->amount, 2, ',', '.') }} RSD
                        @endif
                    </x-ui.table.td>
                    <x-ui.table.td align="right">
                        <x-ui.table.actions>
                            <x-ui.buttons.icon-action
                                wire:click="editItem({{ $item->id }})"
                                :title="__('messages.actions.edit')"
                                color="primary"
                            >
                                <x-ui.icons.pen :class="$actionIconClass" />
                            </x-ui.buttons.icon-action>

                            <x-ui.buttons.icon-action
                                wire:click="deleteItem({{ $item->id }})"
                                :title="__('messages.actions.delete')"
                                color="danger"
                            >
                                <x-ui.icons.trash :class="$actionIconClass" />
                            </x-ui.buttons.icon-action>
                        </x-ui.table.actions>
                    </x-ui.table.td>
                </x-ui.table.row>
            @empty
                <x-ui.table.empty colspan="6">
                    @lang('messages.monthly_expenses.empty')
                </x-ui.table.empty>
            @endforelse
        </x-ui.table.body>
    </x-ui.table>

    <div class="flex items-center justify-end rounded-xl border border-zinc-200 bg-zinc-50 px-4 py-3 text-sm font-semibold dark:border-zinc-700 dark:bg-zinc-900">
        <span>@lang('messages.monthly_expenses.total'): {{ number_format((float) $monthlyTotal, 2, ',', '.') }} RSD</span>
    </div>

    <div>
        {{ $items->links() }}
    </div>
</div>
