<div class="flex h-full w-full flex-1 flex-col gap-6">
    @php($actionIconClass = 'size-3.5')

    <div class="flex flex-col gap-4 md:flex-row md:items-end md:justify-between">
        <div>
            <flux:heading size="xl">@lang('messages.text.clients')</flux:heading>
            <flux:text>@lang('messages.text.clientSubTitle')</flux:text>
        </div>

        <flux:button variant="primary" :href="route('clients.create')" wire:navigate>
            @lang('messages.buttons.add')
        </flux:button>
    </div>

    @if (session('status'))
        <flux:callout variant="success" icon="check-circle">{{ session('status') }}</flux:callout>
    @endif

    @if (session('error'))
        <flux:callout variant="danger" icon="x-circle">{{ session('error') }}</flux:callout>
    @endif

    <div class="grid gap-3 md:grid-cols-3">
        <flux:input
            wire:model.live.debounce.300ms="search"
            label="Pretraga"
            placeholder="Naziv, email ili telefon"
        />

        <flux:select wire:model.live="statusFilter" :label="__('messages.table.status')">
            <option value="all">@lang('messages.text.all')</option>
            <option value="active">@lang('messages.text.active')</option>
            <option value="inactive">@lang('messages.text.inactive')</option>
        </flux:select>
    </div>

    <x-ui.table>
        <x-ui.table.head>
            <tr>
                <x-ui.table.th>@lang('messages.table.name')</x-ui.table.th>
                <x-ui.table.th>@lang('messages.table.type')</x-ui.table.th>
                <x-ui.table.th>@lang('messages.table.contact')</x-ui.table.th>
                <x-ui.table.th>@lang('messages.table.status')</x-ui.table.th>
                <x-ui.table.th align="right">@lang('messages.table.action')</x-ui.table.th>
            </tr>
        </x-ui.table.head>
        <x-ui.table.body>
                @forelse ($clients as $client)
                    <x-ui.table.row wire:key="client-{{ $client->id }}">
                        <x-ui.table.td>
                            <div class="font-medium">
                                <a href="{{ route('clients.show', $client) }}" wire:navigate class="text-blue-600 hover:text-blue-700 hover:underline dark:text-blue-400 dark:hover:text-blue-300">
                                    @if ($client->type?->key === 'person' && $client->person)
                                        {{ trim($client->person->first_name.' '.$client->person->last_name) }}
                                    @else
                                        {{ $client->display_name }}
                                    @endif
                                </a>
                            </div>
                            @if ($client->company && $client->company->pib)
                                <div class="text-xs text-zinc-500">PIB: {{ $client->company->pib }}</div>
                            @endif
                            @if ($client->type?->key !== 'person' && $client->person)
                                <div class="text-xs text-zinc-500">{{ $client->person->first_name }} {{ $client->person->last_name }}</div>
                            @endif
                        </x-ui.table.td>
                        <x-ui.table.td>{{ $client->type->name }}</x-ui.table.td>
                        <x-ui.table.td>
                            <div>{{ $client->email ?: '-' }}</div>
                            <div class="text-xs text-zinc-500">{{ $client->phone ?: '-' }}</div>
                        </x-ui.table.td>
                        <x-ui.table.td>
                            @if ($client->is_active)
                                <span class="inline-flex rounded-full bg-green-100 px-2 py-1 text-xs font-medium text-green-700 dark:bg-green-900/40 dark:text-green-300">Aktivan</span>
                            @else
                                <span class="inline-flex rounded-full bg-zinc-100 px-2 py-1 text-xs font-medium text-zinc-700 dark:bg-zinc-800 dark:text-zinc-300">Neaktivan</span>
                            @endif
                        </x-ui.table.td>
                        <x-ui.table.td align="right">
                            <x-ui.table.actions>
                                <x-ui.buttons.icon-action
                                    :href="route('clients.edit', $client)"
                                    title="Izmeni klijenta"
                                    color="primary"
                                    navigate
                                >
                                    <x-ui.icons.pen :class="$actionIconClass" />
                                </x-ui.buttons.icon-action>

                                <x-ui.buttons.icon-action
                                    wire:click="toggleActive({{ $client->id }})"
                                    :title="$client->is_active ? 'Deaktiviraj klijenta' : 'Aktiviraj klijenta'"
                                    color="warning"
                                >
                                    <x-ui.icons.disable :class="$actionIconClass" />
                                </x-ui.buttons.icon-action>

                                <x-ui.buttons.icon-action
                                    wire:click="deleteClient({{ $client->id }})"
                                    title="Obriši klijenta"
                                    color="danger"
                                >
                                    <x-ui.icons.trash :class="$actionIconClass" />
                                </x-ui.buttons.icon-action>
                            </x-ui.table.actions>
                        </x-ui.table.td>
                    </x-ui.table.row>
                @empty
                    <x-ui.table.empty colspan="5">
                        @lang('messages.table.noResults')
                    </x-ui.table.empty>
                @endforelse
        </x-ui.table.body>
    </x-ui.table>

    <div>
        {{ $clients->links() }}
    </div>
</div>
