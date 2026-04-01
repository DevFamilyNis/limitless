<div class="flex h-full w-full flex-1 flex-col gap-6">
    @php($actionIconClass = 'size-3.5')

    <div class="flex flex-col gap-4 md:flex-row md:items-end md:justify-between">
        <div>
            <flux:heading size="xl">@lang('messages.leads.title')</flux:heading>
            <flux:text>@lang('messages.leads.subtitle')</flux:text>
        </div>

        <flux:button variant="primary" :href="route('leads.create')" wire:navigate>
            @lang('messages.buttons.add')
        </flux:button>
    </div>

    @if (session('status'))
        <flux:callout variant="success" icon="check-circle">{{ session('status') }}</flux:callout>
    @endif

    <div class="grid gap-4 md:grid-cols-2 xl:grid-cols-4">
        <flux:card>
            <flux:text class="text-xs text-zinc-500">@lang('messages.leads.stats_total')</flux:text>
            <flux:heading size="lg">{{ $statistics['total'] }}</flux:heading>
        </flux:card>
        <flux:card>
            <flux:text class="text-xs text-zinc-500">@lang('messages.leads.stats_responded')</flux:text>
            <flux:heading size="lg">{{ $statistics['responded'] }}</flux:heading>
        </flux:card>
        <flux:card>
            <flux:text class="text-xs text-zinc-500">@lang('messages.leads.stats_converted')</flux:text>
            <flux:heading size="lg">{{ $statistics['converted'] }}</flux:heading>
        </flux:card>
        <flux:card>
            <flux:text class="text-xs text-zinc-500">@lang('messages.leads.stats_conversion_rate')</flux:text>
            <flux:heading size="lg">{{ number_format($statistics['conversion_rate'], 1) }}%</flux:heading>
        </flux:card>
    </div>

    <div class="grid gap-3 lg:grid-cols-[minmax(0,1.8fr)_320px]">
        <flux:input
            wire:model.live.debounce.300ms="search"
            :label="__('messages.common.search')"
            :placeholder="__('messages.leads.search_placeholder')"
        />

        <flux:select wire:model.live="statusFilter" :label="__('messages.table.status')">
            <option value="all">@lang('messages.text.all')</option>
            @foreach ($statuses as $status)
                <option value="{{ $status->key }}">{{ $status->name }}</option>
            @endforeach
        </flux:select>
    </div>

    <x-ui.table>
        <x-ui.table.head>
            <tr>
                <x-ui.table.th>@lang('messages.table.name')</x-ui.table.th>
                <x-ui.table.th>@lang('messages.table.contact')</x-ui.table.th>
                <x-ui.table.th>@lang('messages.table.status')</x-ui.table.th>
                <x-ui.table.th>@lang('messages.leads.last_contact')</x-ui.table.th>
                <x-ui.table.th>@lang('messages.leads.next_contact')</x-ui.table.th>
                <x-ui.table.th align="right">@lang('messages.table.action')</x-ui.table.th>
            </tr>
        </x-ui.table.head>
        <x-ui.table.body>
            @forelse ($leads as $lead)
                <x-ui.table.row wire:key="lead-{{ $lead->id }}">
                    @php($nextFollowUp = $lead->current_next_follow_up_at)
                    <x-ui.table.td>
                        <a href="{{ route('leads.show', $lead) }}" wire:navigate class="font-medium text-blue-600 hover:text-blue-700 hover:underline dark:text-blue-400 dark:hover:text-blue-300">
                            {{ $lead->company_name }}
                        </a>
                        <div class="mt-1 text-xs text-zinc-500">
                            @lang('messages.leads.comments'): {{ $lead->comments_count }}
                        </div>
                    </x-ui.table.td>
                    <x-ui.table.td>
                        <div>{{ $lead->email ?: '-' }}</div>
                        <div class="text-xs text-zinc-500">{{ $lead->phone ?: '-' }}</div>
                    </x-ui.table.td>
                    <x-ui.table.td>
                        <flux:badge color="sky">{{ $lead->status?->name ?? '-' }}</flux:badge>
                    </x-ui.table.td>
                    <x-ui.table.td>
                        <div>{{ $lead->last_contacted_at?->format('d.m.Y H:i') ?: '-' }}</div>
                        <div class="text-xs text-zinc-500">{{ $lead->last_contact_method ?: '-' }}</div>
                    </x-ui.table.td>
                    <x-ui.table.td>
                        @if ($nextFollowUp)
                            @php($today = now()->startOfDay())
                            @php($tomorrow = now()->addDay()->startOfDay())
                            @php($nextFollowUpDay = $nextFollowUp->copy()->startOfDay())

                            @if ($nextFollowUpDay->lt($today))
                                @php($badgeClass = 'border-rose-200 bg-rose-50 text-rose-700 dark:border-rose-500/30 dark:bg-rose-500/10 dark:text-rose-200')
                                @php($badgeText = __('messages.leads.badge_overdue'))
                            @elseif ($nextFollowUpDay->equalTo($today))
                                @php($badgeClass = 'border-amber-200 bg-amber-50 text-amber-700 dark:border-amber-500/30 dark:bg-amber-500/10 dark:text-amber-200')
                                @php($badgeText = __('messages.leads.badge_today'))
                            @elseif ($nextFollowUpDay->equalTo($tomorrow))
                                @php($badgeClass = 'border-sky-200 bg-sky-50 text-sky-700 dark:border-sky-500/30 dark:bg-sky-500/10 dark:text-sky-200')
                                @php($badgeText = __('messages.leads.badge_tomorrow'))
                            @else
                                @php($badgeClass = 'border-emerald-200 bg-emerald-50 text-emerald-700 dark:border-emerald-500/30 dark:bg-emerald-500/10 dark:text-emerald-200')
                                @php($badgeText = __('messages.leads.badge_scheduled'))
                            @endif

                            <div class="inline-flex rounded-full border px-3 py-1 text-sm font-semibold {{ $badgeClass }}">
                                {{ $badgeText }}
                            </div>
                            <div class="mt-2 font-medium text-zinc-800 dark:text-zinc-100">
                                {{ $nextFollowUp->format('d.m.Y H:i') }}
                            </div>
                            <div class="text-xs text-zinc-500">
                                {{ $nextFollowUp->isPast() && ! $nextFollowUpDay->equalTo($today) ? __('messages.leads.overdue_next_contact') : __('messages.leads.scheduled_next_contact') }}
                            </div>
                        @else
                            <span class="text-zinc-400">-</span>
                        @endif
                    </x-ui.table.td>
                    <x-ui.table.td align="right">
                        <x-ui.table.actions>
                            <x-ui.buttons.icon-action
                                :href="route('leads.show', $lead)"
                                :title="__('messages.actions.open')"
                                color="primary"
                                navigate
                            >
                                <x-ui.icons.chat-bubble :class="$actionIconClass" />
                            </x-ui.buttons.icon-action>

                            <x-ui.buttons.icon-action
                                :href="route('leads.edit', $lead)"
                                :title="__('messages.leads.edit_title')"
                                color="warning"
                                navigate
                            >
                                <x-ui.icons.pen :class="$actionIconClass" />
                            </x-ui.buttons.icon-action>

                            <x-ui.buttons.icon-action
                                wire:click="deleteLead({{ $lead->id }})"
                                :title="__('messages.leads.delete_title')"
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
        {{ $leads->links() }}
    </div>
</div>
