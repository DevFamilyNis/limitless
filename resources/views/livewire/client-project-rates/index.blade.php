<div class="flex h-full w-full flex-1 flex-col gap-6">
    @php($actionIconClass = 'size-3.5')

    <div class="flex flex-col gap-4 md:flex-row md:items-end md:justify-between">
        <div>
            <flux:heading size="xl">@lang('messages.client_project_rates.title')</flux:heading>
            <flux:text>@lang('messages.client_project_rates.subtitle')</flux:text>
        </div>

        <flux:button variant="primary" :href="route('client-project-rates.create')" wire:navigate>
            @lang('messages.actions.add')
        </flux:button>
    </div>

    @if (session('status'))
        <flux:callout variant="success" icon="check-circle">{{ session('status') }}</flux:callout>
    @endif

    <div class="grid gap-3 md:grid-cols-3">
        <flux:input wire:model.live.debounce.300ms="search" :label="__('messages.common.search')" :placeholder="__('messages.client_project_rates.search_placeholder')" />

        <flux:select wire:model.live="statusFilter" :label="__('messages.common.status')">
            <option value="all">@lang('messages.common.all')</option>
            <option value="active">@lang('messages.status_labels.active_m')</option>
            <option value="inactive">@lang('messages.status_labels.inactive_m')</option>
        </flux:select>
    </div>

    <x-ui.table>
        <x-ui.table.head>
            <tr>
                <x-ui.table.th>@lang('messages.client_project_rates.client')</x-ui.table.th>
                <x-ui.table.th>@lang('messages.client_project_rates.project')</x-ui.table.th>
                <x-ui.table.th>@lang('messages.table.period')</x-ui.table.th>
                <x-ui.table.th>@lang('messages.client_project_rates.price')</x-ui.table.th>
                <x-ui.table.th>@lang('messages.client_project_rates.status')</x-ui.table.th>
                <x-ui.table.th align="right">@lang('messages.common.action')</x-ui.table.th>
            </tr>
        </x-ui.table.head>
        <x-ui.table.body>
                @forelse ($rates as $rate)
                    @php($projectColor = $rate->project ? \App\Support\ProjectColorPalette::for($rate->project) : null)
                    <x-ui.table.row wire:key="client-project-rate-{{ $rate->id }}">
                        <x-ui.table.td>
                            @if ($rate->client?->type?->key === 'person' && $rate->client?->person)
                                {{ trim($rate->client->person->first_name.' '.$rate->client->person->last_name) }}
                            @else
                                {{ $rate->client?->display_name }}
                            @endif
                        </x-ui.table.td>
                        <x-ui.table.td>
                            <div
                                class="inline-flex rounded-full border px-2 py-1 text-xs font-semibold"
                                @if ($projectColor)
                                    style="background-color: {{ $projectColor['soft_bg'] }}; border-color: {{ $projectColor['border'] }}; color: {{ $projectColor['hex'] }};"
                                @endif
                            >
                                {{ $rate->project?->code }} - {{ $rate->project?->name }}
                            </div>
                        </x-ui.table.td>
                        <x-ui.table.td>{{ $rate->billingPeriod?->name }}</x-ui.table.td>
                        <x-ui.table.td>{{ number_format((float) $rate->price_amount, 2, ',', '.') }} {{ $rate->currency }}</x-ui.table.td>
                        <x-ui.table.td>
                            @if ($rate->is_active)
                                <span class="inline-flex rounded-full bg-green-100 px-2 py-1 text-xs font-medium text-green-700 dark:bg-green-900/40 dark:text-green-300">@lang('messages.status_labels.active_f')</span>
                            @else
                                <span class="inline-flex rounded-full bg-zinc-100 px-2 py-1 text-xs font-medium text-zinc-700 dark:bg-zinc-800 dark:text-zinc-300">@lang('messages.status_labels.inactive_f')</span>
                            @endif
                        </x-ui.table.td>
                        <x-ui.table.td align="right">
                            <x-ui.table.actions>
                                <x-ui.buttons.icon-action
                                    :href="route('client-project-rates.edit', $rate)"
                                    :title="__('messages.client_project_rates.edit_action')"
                                    color="primary"
                                    navigate
                                >
                                    <x-ui.icons.pen :class="$actionIconClass" />
                                </x-ui.buttons.icon-action>

                                <x-ui.buttons.icon-action
                                    wire:click="toggleActive({{ $rate->id }})"
                                    :title="$rate->is_active ? __('messages.client_project_rates.toggle_deactivate') : __('messages.client_project_rates.toggle_activate')"
                                    color="warning"
                                >
                                    <x-ui.icons.disable :class="$actionIconClass" />
                                </x-ui.buttons.icon-action>

                                <x-ui.buttons.icon-action
                                    wire:click="deleteRate({{ $rate->id }})"
                                    :title="__('messages.client_project_rates.delete_action')"
                                    color="danger"
                                >
                                    <x-ui.icons.trash :class="$actionIconClass" />
                                </x-ui.buttons.icon-action>
                            </x-ui.table.actions>
                        </x-ui.table.td>
                    </x-ui.table.row>
                @empty
                    <x-ui.table.empty colspan="6">
                        @lang('messages.client_project_rates.empty')
                    </x-ui.table.empty>
                @endforelse
        </x-ui.table.body>
    </x-ui.table>

    <div>
        {{ $rates->links() }}
    </div>
</div>
