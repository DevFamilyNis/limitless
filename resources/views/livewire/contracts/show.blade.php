<div class="mx-auto flex w-full max-w-5xl flex-col gap-6">
    @php($actionIconClass = 'size-3.5')

    <div class="flex flex-col gap-4 md:flex-row md:items-start md:justify-between">
        <div>
            <div class="mb-1 flex items-center gap-2">
                <flux:heading size="xl">{{ $contract->client->display_name }}</flux:heading>
                <x-ui.badge color="{{ $contract->type->value === 'Ugovor' ? 'blue' : 'amber' }}">
                    {{ $contract->type->value }}
                </x-ui.badge>
                <x-ui.badge color="{{ $contract->status->value === 'Aktivan' ? 'emerald' : 'zinc' }}">
                    {{ $contract->status->value }}
                </x-ui.badge>
            </div>
            <flux:text>
                {{ $contract->start_date->format('d.m.Y') }}
                @if ($contract->end_date) — {{ $contract->end_date->format('d.m.Y') }} @endif
            </flux:text>
        </div>

        <div class="flex flex-wrap items-center gap-2">
            @if ($contract->getFirstMedia('pdf'))
                <flux:button :href="route('contracts.pdf', $contract)" target="_blank" icon="eye" variant="ghost">
                    @lang('messages.contracts.view_pdf')
                </flux:button>
            @endif

            @if ($isAktivan)
                <flux:button wire:click="changeStatus('Neaktivan')" variant="ghost">
                    @lang('messages.contracts.deactivate')
                </flux:button>
            @else
                <flux:button wire:click="changeStatus('Aktivan')" variant="ghost">
                    @lang('messages.contracts.activate')
                </flux:button>
            @endif

            <flux:button variant="primary" :href="route('contracts.edit', $contract)" wire:navigate>
                @lang('messages.buttons.edit')
            </flux:button>

            <flux:button variant="ghost" :href="route('contracts.index')" wire:navigate>
                @lang('messages.buttons.back')
            </flux:button>
        </div>
    </div>

    @if (session('status'))
        <flux:callout variant="success" icon="check-circle">{{ session('status') }}</flux:callout>
    @endif

    @if (session('error'))
        <flux:callout variant="danger" icon="x-circle">{{ session('error') }}</flux:callout>
    @endif

    @if (! $isUgovor && $contract->parentContract)
        <flux:card>
            <flux:heading size="sm" class="mb-3">@lang('messages.contracts.parent_contract')</flux:heading>
            <div class="flex items-center gap-3">
                <a href="{{ route('contracts.show', $contract->parentContract) }}" wire:navigate
                   class="font-medium text-blue-600 hover:underline dark:text-blue-400">
                    {{ $contract->parentContract->client->display_name }}
                </a>
                <x-ui.badge color="{{ $contract->parentContract->status->value === 'Aktivan' ? 'emerald' : 'zinc' }}">
                    {{ $contract->parentContract->status->value }}
                </x-ui.badge>
                <flux:text class="text-sm text-zinc-500">
                    {{ $contract->parentContract->start_date->format('d.m.Y') }}
                    @if ($contract->parentContract->end_date)
                        — {{ $contract->parentContract->end_date->format('d.m.Y') }}
                    @endif
                </flux:text>
            </div>
        </flux:card>
    @endif

    @if ($contract->note)
        <flux:card>
            <flux:heading size="sm" class="mb-2">@lang('messages.contracts.note')</flux:heading>
            <flux:text class="whitespace-pre-wrap">{{ $contract->note }}</flux:text>
        </flux:card>
    @endif

    @if ($isUgovor)
        <flux:card>
            <div class="mb-4 flex items-center justify-between">
                <flux:heading size="sm">@lang('messages.contracts.annexes')</flux:heading>
                <flux:button
                    variant="ghost"
                    size="sm"
                    :href="route('contracts.create', ['type' => 'Aneks', 'client_id' => $contract->client_id, 'parent_id' => $contract->id])"
                    wire:navigate
                >
                    @lang('messages.contracts.add_annex')
                </flux:button>
            </div>

            @if ($contract->annexes->isEmpty())
                <flux:text class="text-sm text-zinc-500">@lang('messages.contracts.no_annexes')</flux:text>
            @else
                <x-ui.table>
                    <x-ui.table.head>
                        <tr>
                            <x-ui.table.th>@lang('messages.table.status')</x-ui.table.th>
                            <x-ui.table.th>@lang('messages.contracts.start_date')</x-ui.table.th>
                            <x-ui.table.th>@lang('messages.contracts.end_date')</x-ui.table.th>
                            <x-ui.table.th align="right">@lang('messages.table.action')</x-ui.table.th>
                        </tr>
                    </x-ui.table.head>
                    <x-ui.table.body>
                        @foreach ($contract->annexes as $annex)
                            <x-ui.table.row wire:key="annex-{{ $annex->id }}">
                                <x-ui.table.td>
                                    <x-ui.badge color="{{ $annex->status->value === 'Aktivan' ? 'emerald' : 'zinc' }}">
                                        {{ $annex->status->value }}
                                    </x-ui.badge>
                                </x-ui.table.td>
                                <x-ui.table.td>{{ $annex->start_date->format('d.m.Y') }}</x-ui.table.td>
                                <x-ui.table.td>{{ $annex->end_date?->format('d.m.Y') ?? '—' }}</x-ui.table.td>
                                <x-ui.table.td align="right">
                                    <x-ui.table.actions>
                                        <x-ui.buttons.icon-action
                                            :href="route('contracts.show', $annex)"
                                            :title="__('messages.actions.open')"
                                            color="primary"
                                            navigate
                                        >
                                            <x-ui.icons.eye :class="$actionIconClass" />
                                        </x-ui.buttons.icon-action>
                                        <x-ui.buttons.icon-action
                                            :href="route('contracts.edit', $annex)"
                                            :title="__('messages.contracts.edit_title')"
                                            color="warning"
                                            navigate
                                        >
                                            <x-ui.icons.pen :class="$actionIconClass" />
                                        </x-ui.buttons.icon-action>
                                    </x-ui.table.actions>
                                </x-ui.table.td>
                            </x-ui.table.row>
                        @endforeach
                    </x-ui.table.body>
                </x-ui.table>
            @endif
        </flux:card>
    @endif
</div>
