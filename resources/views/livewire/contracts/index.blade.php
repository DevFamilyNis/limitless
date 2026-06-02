<div class="flex h-full w-full flex-1 flex-col gap-6">
    @php($actionIconClass = 'size-3.5')

    <div class="flex flex-col gap-4 md:flex-row md:items-end md:justify-between">
        <div>
            <flux:heading size="xl">@lang('messages.contracts.title')</flux:heading>
            <flux:text>@lang('messages.contracts.subtitle')</flux:text>
        </div>

        <flux:button variant="primary" :href="route('contracts.create')" wire:navigate>
            @lang('messages.buttons.add')
        </flux:button>
    </div>

    @if (session('status'))
        <flux:callout variant="success" icon="check-circle">{{ session('status') }}</flux:callout>
    @endif

    <div class="grid gap-3 md:grid-cols-3">
        <flux:select wire:model.live="clientFilter" :label="__('messages.contracts.client')">
            <option value="">@lang('messages.common.all')</option>
            @foreach ($clients as $client)
                <option value="{{ $client->id }}">{{ $client->display_name }}</option>
            @endforeach
        </flux:select>

        <flux:select wire:model.live="typeFilter" :label="__('messages.contracts.type')">
            <option value="">@lang('messages.common.all')</option>
            @foreach ($types as $type)
                <option value="{{ $type->value }}">{{ $type->value }}</option>
            @endforeach
        </flux:select>

        <flux:select wire:model.live="statusFilter" :label="__('messages.table.status')">
            <option value="">@lang('messages.common.all')</option>
            @foreach ($statuses as $status)
                <option value="{{ $status->value }}">{{ $status->value }}</option>
            @endforeach
        </flux:select>
    </div>

    <x-ui.table>
        <x-ui.table.head>
            <tr>
                <x-ui.table.th>@lang('messages.contracts.client')</x-ui.table.th>
                <x-ui.table.th>@lang('messages.contracts.type')</x-ui.table.th>
                <x-ui.table.th>@lang('messages.table.status')</x-ui.table.th>
                <x-ui.table.th>@lang('messages.contracts.start_date')</x-ui.table.th>
                <x-ui.table.th>@lang('messages.contracts.end_date')</x-ui.table.th>
                <x-ui.table.th align="right">@lang('messages.table.action')</x-ui.table.th>
            </tr>
        </x-ui.table.head>
        <x-ui.table.body>
            @forelse ($contracts as $contract)
                <x-ui.table.row wire:key="contract-{{ $contract->id }}">
                    <x-ui.table.td>
                        <a href="{{ route('contracts.show', $contract) }}" wire:navigate
                           class="font-medium text-blue-600 hover:text-blue-700 hover:underline dark:text-blue-400 dark:hover:text-blue-300">
                            {{ $contract->client->display_name }}
                        </a>
                    </x-ui.table.td>
                    <x-ui.table.td>
                        <x-ui.badge color="{{ $contract->type->value === 'Ugovor' ? 'blue' : 'amber' }}">
                            {{ $contract->type->value }}
                        </x-ui.badge>
                    </x-ui.table.td>
                    <x-ui.table.td>
                        <x-ui.badge color="{{ $contract->status->value === 'Aktivan' ? 'emerald' : 'zinc' }}">
                            {{ $contract->status->value }}
                        </x-ui.badge>
                    </x-ui.table.td>
                    <x-ui.table.td>
                        {{ $contract->start_date->format('d.m.Y') }}
                    </x-ui.table.td>
                    <x-ui.table.td>
                        {{ $contract->end_date?->format('d.m.Y') ?? '—' }}
                    </x-ui.table.td>
                    <x-ui.table.td align="right">
                        <x-ui.table.actions>
                            <x-ui.buttons.icon-action
                                :href="route('contracts.show', $contract)"
                                :title="__('messages.actions.open')"
                                color="primary"
                                navigate
                            >
                                <x-ui.icons.eye :class="$actionIconClass" />
                            </x-ui.buttons.icon-action>

                            <x-ui.buttons.icon-action
                                :href="route('contracts.edit', $contract)"
                                :title="__('messages.contracts.edit_title')"
                                color="warning"
                                navigate
                            >
                                <x-ui.icons.pen :class="$actionIconClass" />
                            </x-ui.buttons.icon-action>

                            <x-ui.buttons.icon-action
                                wire:click="deleteContract({{ $contract->id }})"
                                wire:confirm="@lang('messages.contracts.confirm_delete')"
                                :title="__('messages.contracts.delete_title')"
                                color="danger"
                            >
                                <x-ui.icons.trash :class="$actionIconClass" />
                            </x-ui.buttons.icon-action>
                        </x-ui.table.actions>
                    </x-ui.table.td>
                </x-ui.table.row>
            @empty
                <x-ui.table.empty colspan="6">@lang('messages.table.noResults')</x-ui.table.empty>
            @endforelse
        </x-ui.table.body>
    </x-ui.table>

    <div>
        {{ $contracts->links() }}
    </div>
</div>
