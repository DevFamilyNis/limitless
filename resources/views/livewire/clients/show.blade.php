<div class="mx-auto flex w-full max-w-6xl flex-col gap-6">
    <div class="flex flex-col gap-4 md:flex-row md:items-start md:justify-between">
        <div>
            <flux:heading size="xl">{{ $clientName }}</flux:heading>
            <flux:text>@lang('messages.text.clientShowSubTitle')</flux:text>
        </div>

        <div class="flex items-center gap-2">
            <flux:button variant="ghost" :href="route('clients.index')" wire:navigate>
                @lang('messages.buttons.back')
            </flux:button>
            <flux:button variant="primary" :href="route('clients.edit', $client)" wire:navigate>
                @lang('messages.buttons.edit')
            </flux:button>
        </div>
    </div>

    <div class="grid gap-4 md:grid-cols-4">
        <flux:card>
            <flux:text class="text-xs text-zinc-500">@lang('messages.menu.invoices')</flux:text>
            <flux:heading size="lg">{{ $client->invoices_count }}</flux:heading>
        </flux:card>
        <flux:card>
            <flux:text class="text-xs text-zinc-500">@lang('messages.menu.transaction')</flux:text>
            <flux:heading size="lg">{{ $client->transactions_count }}</flux:heading>
        </flux:card>
        <flux:card>
            <flux:text class="text-xs text-zinc-500">@lang('messages.menu.projectsPrice')</flux:text>
            <flux:heading size="lg">{{ $client->project_rates_count }}</flux:heading>
        </flux:card>
        <flux:card>
            <flux:text class="text-xs text-zinc-500">@lang('messages.text.issues')</flux:text>
            <flux:heading size="lg">{{ $client->issues_count }}</flux:heading>
        </flux:card>
    </div>

    <div class="grid gap-6 lg:grid-cols-2">
        <flux:card class="space-y-3">
            <flux:heading size="lg">@lang('messages.text.companyInfo')</flux:heading>

            <div class="grid gap-3 text-sm">
                <div><span class="text-zinc-500">@lang('messages.table.type'):
                    </span> {{ $client->type?->name ?? '-' }}</div>
                <div><span class="text-zinc-500">@lang('messages.table.status'):
                    </span> {{ $client->is_active ? __('messages.status_labels.active_m') : __('messages.status_labels.inactive_m') }}</div>
                <div><span class="text-zinc-500">@lang('messages.form.email'):
                    </span> {{ $client->email ?: '-' }}</div>
                <div><span class="text-zinc-500">@lang('messages.form.phone'):
                    </span> {{ $client->phone ?: '-' }}</div>
                <div><span class="text-zinc-500">@lang('messages.form.address'):
                    </span> {{ $client->address ?: '-' }}</div>
                <div>
                    <span class="text-zinc-500">@lang('messages.form.link'):</span>
                    @if ($client->appLinks->isNotEmpty())
                        <div class="mt-1 space-y-1">
                            @foreach ($client->appLinks as $appLink)
                                <div class="text-sm">
                                    @if ($appLink->label)
                                        <span class="font-medium">{{ $appLink->label }}:</span>
                                    @endif
                                    <a href="{{ $appLink->url }}" target="_blank" rel="noopener noreferrer" class="text-blue-600 underline hover:text-blue-700 dark:text-blue-400 dark:hover:text-blue-300">
                                        {{ $appLink->url }}
                                    </a>
                                </div>
                            @endforeach
                        </div>
                    @elseif ($client->app_link)
                        <a href="{{ $client->app_link }}" target="_blank" rel="noopener noreferrer" class="text-blue-600 underline hover:text-blue-700 dark:text-blue-400 dark:hover:text-blue-300">
                            {{ $client->app_link }}
                        </a>
                    @else
                        -
                    @endif
                </div>
                <div><span class="text-zinc-500">@lang('messages.form.note'):
                    </span> {{ $client->note ?: '-' }}</div>
            </div>
        </flux:card>

        <flux:card class="space-y-3">
            <flux:heading size="lg">@lang('messages.text.mainInfo')</flux:heading>

            @if ($client->type?->key === 'person')
                <div class="grid gap-3 text-sm">
                    <div><span class="text-zinc-500">@lang('messages.form.name'):</span>
                        {{ $client->person?->first_name ?: '-' }}
                    </div>
                    <div><span class="text-zinc-500">@lang('messages.form.surname'):</span>
                        {{ $client->person?->last_name ?: '-' }}
                    </div>
                </div>
            @else
                <div class="grid gap-3 text-sm">
                    <div><span class="text-zinc-500">@lang('messages.form.pib'):</span>
                        <flux:badge color="lime">
                             {{ $client->company?->pib ?: '-' }}
                        </flux:badge>
                    </div>
                    <div><span class="text-zinc-500">@lang('messages.form.mb'):</span>
                        <flux:badge color="lime">
                            {{ $client->company?->mb ?: '-' }}
                         </flux:badge>
                    </div>
                    <div><span class="text-zinc-500">@lang('messages.form.account'):</span>
                         <flux:badge color="lime">
                            {{ $client->company?->bank_account ?: '-' }}
                         </flux:badge>
                    </div>
                </div>
            @endif
        </flux:card>
    </div>

    @if ($client->contacts->isNotEmpty())
        <flux:card class="space-y-4">
            <flux:heading size="lg">@lang('messages.table.contact')</flux:heading>

            <x-ui.table>
                <x-ui.table.head>
                    <tr>
                        <x-ui.table.th>@lang('messages.form.nameSurname')</x-ui.table.th>
                        <x-ui.table.th>@lang('messages.form.position')</x-ui.table.th>
                        <x-ui.table.th>@lang('messages.table.contact')</x-ui.table.th>
                        <x-ui.table.th>@lang('messages.table.main')</x-ui.table.th>
                    </tr>
                </x-ui.table.head>
                <x-ui.table.body>
                    @foreach ($client->contacts as $contact)
                        <x-ui.table.row>
                            <x-ui.table.td class="font-bold">
                                {{ $contact->full_name }}
                            </x-ui.table.td>
                            <x-ui.table.td>{{ $contact->position ?: '-' }}</x-ui.table.td>
                            <x-ui.table.td>
                                <div>{{ $contact->email ?: '-' }}</div>
                                <div class="text-xs text-zinc-500">{{ $contact->phone ?: '-' }}</div>
                            </x-ui.table.td>
                            <x-ui.table.td>{{ $contact->is_primary ? __('messages.common.yes') : __('messages.common.no') }}</x-ui.table.td>
                        </x-ui.table.row>
                    @endforeach
                </x-ui.table.body>
            </x-ui.table>
        </flux:card>
    @endif

    <flux:card class="space-y-4">
        <flux:heading size="lg">@lang('messages.menu.projectsPrice')</flux:heading>

        <x-ui.table>
            <x-ui.table.head>
                <tr>
                    <x-ui.table.th>@lang('messages.table.service')</x-ui.table.th>
                    <x-ui.table.th>@lang('messages.table.period')</x-ui.table.th>
                    <x-ui.table.th>@lang('messages.table.price')</x-ui.table.th>
                    <x-ui.table.th>@lang('messages.table.status')</x-ui.table.th>
                </tr>
            </x-ui.table.head>
            <x-ui.table.body>
                @forelse ($client->projectRates as $rate)
                    @php($projectColor = $rate->project ? \App\Support\ProjectColorPalette::for($rate->project) : null)
                    <x-ui.table.row>
                        <x-ui.table.td class="font-medium">
                            @if ($rate->project)
                                <span
                                    class="inline-flex rounded-full border px-2 py-1 text-xs font-semibold"
                                    @if ($projectColor)
                                        style="background-color: {{ $projectColor['soft_bg'] }}; border-color: {{ $projectColor['border'] }}; color: {{ $projectColor['hex'] }};"
                                    @endif
                                >
                                    {{ $rate->project->name }}
                                </span>
                            @else
                                -
                            @endif
                        </x-ui.table.td>
                        <x-ui.table.td>{{ $rate->billingPeriod?->name ?? '-' }}</x-ui.table.td>
                        <x-ui.table.td>{{ number_format((float) $rate->price_amount, 2, ',', '.') }} {{ $rate->currency }}</x-ui.table.td>
                        <x-ui.table.td>{{ $rate->is_active ? __('messages.status_labels.active_m') : __('messages.status_labels.inactive_m') }}</x-ui.table.td>
                    </x-ui.table.row>
                @empty
                    <x-ui.table.empty colspan="4">@lang('messages.table.noResults')</x-ui.table.empty>
                @endforelse
            </x-ui.table.body>
        </x-ui.table>
    </flux:card>
</div>
