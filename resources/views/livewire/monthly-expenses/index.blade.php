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

    <div class="grid gap-3 md:grid-cols-3">
        <flux:select wire:model.live="month" :label="__('messages.common.month')">
            @foreach ($months as $monthKey => $monthName)
                <option value="{{ $monthKey }}">{{ ucfirst($monthName) }}</option>
            @endforeach
        </flux:select>
        <flux:select wire:model.live="year" :label="__('messages.common.year')">
            @foreach ($years as $yearKey => $yearName)
                <option value="{{ $yearKey }}">{{ $yearName }}</option>
            @endforeach
        </flux:select>
        <flux:input wire:model.live.debounce.300ms="search" :label="__('messages.common.search')" :placeholder="__('messages.monthly_expenses.search_placeholder')" />
    </div>

    <div class="rounded-xl border border-zinc-200 p-4 dark:border-zinc-700">
        <div class="mb-4 flex items-center justify-between gap-3">
            <flux:heading size="sm">
                {{ $editingExpenseId ? __('messages.monthly_expenses.edit_title') : __('messages.monthly_expenses.new_title') }}
            </flux:heading>
            @if ($editingExpenseId)
                <flux:button variant="ghost" wire:click="cancelEditing">@lang('messages.actions.back')</flux:button>
            @endif
        </div>

        @if ($expenseCategories->isEmpty())
            <flux:callout variant="warning" icon="exclamation-triangle">
                @lang('messages.monthly_expenses.requirements')
            </flux:callout>
        @else
            <form wire:submit="saveExpense" class="grid gap-3 md:grid-cols-2">
                <flux:select wire:model="categoryId" :label="__('messages.transactions.category')" required>
                    @foreach ($expenseCategories as $category)
                        <option value="{{ $category->id }}">{{ $category->name }}</option>
                    @endforeach
                </flux:select>

                <flux:input wire:model="date" :label="__('messages.common.date')" type="date" required />
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
                <x-ui.table.th>@lang('messages.common.date')</x-ui.table.th>
                <x-ui.table.th>@lang('messages.common.title')</x-ui.table.th>
                <x-ui.table.th>@lang('messages.transactions.category')</x-ui.table.th>
                <x-ui.table.th>@lang('messages.form.note')</x-ui.table.th>
                <x-ui.table.th align="right">@lang('messages.common.amount')</x-ui.table.th>
                <x-ui.table.th align="right">@lang('messages.common.action')</x-ui.table.th>
            </tr>
        </x-ui.table.head>
        <x-ui.table.body>
            @forelse ($expenses as $expense)
                <x-ui.table.row wire:key="monthly-expense-{{ $expense->id }}">
                    <x-ui.table.td>{{ $expense->date?->format('d.m.Y') }}</x-ui.table.td>
                    <x-ui.table.td class="font-medium">{{ $expense->title }}</x-ui.table.td>
                    <x-ui.table.td>{{ $expense->category?->name }}</x-ui.table.td>
                    <x-ui.table.td>{{ $expense->note ?: '-' }}</x-ui.table.td>
                    <x-ui.table.td align="right">{{ number_format((float) $expense->amount, 2, ',', '.') }} {{ $expense->currency }}</x-ui.table.td>
                    <x-ui.table.td align="right">
                        <x-ui.table.actions>
                            <x-ui.buttons.icon-action
                                wire:click="editExpense({{ $expense->id }})"
                                :title="__('messages.actions.edit')"
                                color="primary"
                            >
                                <x-ui.icons.pen :class="$actionIconClass" />
                            </x-ui.buttons.icon-action>

                            <x-ui.buttons.icon-action
                                wire:click="deleteExpense({{ $expense->id }})"
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
        <span>@lang('messages.monthly_expenses.total'): {{ number_format((float) $totalAmount, 2, ',', '.') }} RSD</span>
    </div>

    <div>
        {{ $expenses->links() }}
    </div>
</div>
