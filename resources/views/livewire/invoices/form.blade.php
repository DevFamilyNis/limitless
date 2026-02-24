<div class="flex h-full w-full flex-1 flex-col gap-6">
    <div class="mb-6 flex items-center justify-between">
        <div>
            <flux:heading size="xl">{{ $isEditing ? __('messages.invoices.edit_title') : __('messages.invoices.new_title') }}</flux:heading>
            <flux:text>@lang('messages.invoices.form_subtitle')</flux:text>
        </div>

        <flux:button variant="ghost" :href="route('invoices.index')" wire:navigate>
            @lang('messages.actions.back')
        </flux:button>
    </div>

    @unless ($hasRequiredData)
        <flux:callout variant="warning" icon="exclamation-triangle" class="mb-6">
            @lang('messages.invoices.requirements')
        </flux:callout>
    @endunless

    <form wire:submit="save" class="space-y-6">
        <div class="grid gap-4 md:grid-cols-3">
            <flux:input wire:model="invoiceNumber" :label="__('messages.invoices.number_label')" readonly />
            <flux:input wire:model="invoiceYear" :label="__('messages.common.year')" readonly />
            <flux:input wire:model="invoiceSeq" :label="__('messages.invoices.sequence')" readonly />
        </div>

        <div class="grid gap-4 md:grid-cols-2 lg:grid-cols-4">
            <flux:select wire:model.live="clientId" :label="__('messages.invoices.client')" required>
                <option value="">@lang('messages.invoices.select_client')</option>
                @foreach ($clients as $client)
                    <option value="{{ $client->id }}">
                        @if ($client->type?->key === 'person' && $client->person)
                            {{ trim($client->person->first_name.' '.$client->person->last_name) }}
                        @else
                            {{ $client->display_name }}
                        @endif
                    </option>
                @endforeach
            </flux:select>

            <flux:select wire:model.live="statusId" :label="__('messages.invoices.status_label')" required>
                <option value="">@lang('messages.invoices.select_status')</option>
                @foreach ($statuses as $status)
                    <option value="{{ $status->id }}">{{ $status->name }}</option>
                @endforeach
            </flux:select>
            <flux:input wire:model="issueDate" :label="__('messages.invoices.issue_date')" type="date" required />
            <flux:input wire:model="dueDate" :label="__('messages.invoices.due_date')" type="date" />
        </div>

        <div>
            <flux:heading size="lg" class="mb-3">@lang('messages.invoices.services')</flux:heading>

            @if (! $hasClientPriceList)
                <flux:callout variant="warning" icon="exclamation-triangle">
                    @lang('messages.invoices.no_price_list')
                </flux:callout>
            @else
                <x-ui.table rounded="lg">
                    <x-ui.table.head>
                        <tr>
                            <x-ui.table.th>@lang('messages.invoices.service')</x-ui.table.th>
                            <x-ui.table.th>@lang('messages.invoices.quantity')</x-ui.table.th>
                            <x-ui.table.th>@lang('messages.invoices.price')</x-ui.table.th>
                            <x-ui.table.th>@lang('messages.invoices.amount')</x-ui.table.th>
                            <x-ui.table.th align="right">@lang('messages.common.action')</x-ui.table.th>
                        </tr>
                    </x-ui.table.head>
                    <x-ui.table.body>
                            @foreach ($items as $index => $item)
                                <x-ui.table.row wire:key="invoice-item-{{ $item['id'] ?? 'new-'.$index }}">
                                    <x-ui.table.td>{{ $item['description'] }}</x-ui.table.td>
                                    <x-ui.table.td>{{ number_format((float) $item['quantity'], 2, ',', '.') }}</x-ui.table.td>
                                    <x-ui.table.td>{{ number_format((float) $item['unitPrice'], 2, ',', '.') }}</x-ui.table.td>
                                    <x-ui.table.td>{{ number_format((float) $item['amount'], 2, ',', '.') }}</x-ui.table.td>
                                    <x-ui.table.td align="right">
                                        <x-ui.buttons.icon-action
                                            wire:click="removeItem({{ $index }})"
                                            :title="__('messages.invoices.remove_item')"
                                            color="danger"
                                        >
                                            <x-ui.icons.trash class="size-4" />
                                        </x-ui.buttons.icon-action>
                                    </x-ui.table.td>
                                </x-ui.table.row>
                            @endforeach
                    </x-ui.table.body>
                        <tfoot class="bg-zinc-50 dark:bg-zinc-900/40">
                            <tr>
                                <td class="px-4 py-3 text-right font-medium" colspan="3">@lang('messages.invoices.total')</td>
                                <td class="px-4 py-3 font-semibold">{{ number_format((float) $total, 2, ',', '.') }}</td>
                                <td class="px-4 py-3"></td>
                            </tr>
                        </tfoot>
                </x-ui.table>
            @endif
        </div>

        <flux:textarea wire:model="note" :label="__('messages.invoices.note')" rows="3" />

        <div class="flex items-center gap-3">
            <flux:button variant="primary" type="submit" :disabled="! $hasRequiredData">
                @lang('messages.actions.save')
            </flux:button>
        </div>
    </form>
</div>
