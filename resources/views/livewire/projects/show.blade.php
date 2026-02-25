<div class="mx-auto flex w-full max-w-6xl flex-col gap-6">
    @php($projectColor = \App\Support\ProjectColorPalette::for($project))

    <div class="flex flex-col gap-4 md:flex-row md:items-start md:justify-between">
        <div>
            <flux:heading size="xl" style="color: {{ $projectColor['hex'] }};">{{ $project->name }}</flux:heading>
            <flux:text>@lang('messages.common.code'): {{ $project->code }} | @lang('messages.projects.details_subtitle')</flux:text>
        </div>

        <div class="flex items-center gap-2">
            <flux:button variant="ghost" :href="route('projects.index')" wire:navigate>
                @lang('messages.actions.back')
            </flux:button>
            <flux:button variant="primary" :href="route('projects.edit', $project)" wire:navigate>
                @lang('messages.actions.edit')
            </flux:button>
        </div>
    </div>

    <div class="grid gap-4 md:grid-cols-3">
        <flux:card style="border-color: {{ $projectColor['border'] }}; background-color: {{ $projectColor['soft_bg'] }};">
            <flux:text class="text-xs text-zinc-500">@lang('messages.common.status')</flux:text>
            <flux:heading size="lg" style="color: {{ $projectColor['hex'] }};">{{ $project->is_active ? __('messages.status_labels.active_m') : __('messages.status_labels.inactive_m') }}</flux:heading>
        </flux:card>

        <flux:card>
            <flux:text class="text-xs text-zinc-500">@lang('messages.common.owner')</flux:text>
            <flux:heading size="lg">{{ $project->user?->name ?? '-' }}</flux:heading>
        </flux:card>

        <flux:card>
            <flux:text class="text-xs text-zinc-500">@lang('messages.projects.summary_current_month')</flux:text>
            <flux:heading size="lg">{{ number_format($currentMonthInvoiceTotal, 2, ',', '.') }} RSD</flux:heading>
        </flux:card>
    </div>

    <flux:card class="space-y-3">
        <flux:heading size="lg">@lang('messages.common.description')</flux:heading>
        <flux:text>{{ $project->description ?: '-' }}</flux:text>
    </flux:card>

    <div class="grid gap-6 lg:grid-cols-2">
        <flux:card class="space-y-4">
            <flux:heading size="lg">@lang('messages.projects.project_users')</flux:heading>

            @if ($clients->isEmpty())
                <flux:text class="text-zinc-500">@lang('messages.projects.no_related_users')</flux:text>
            @else
                <x-ui.table>
                    <x-ui.table.head>
                        <tr>
                            <x-ui.table.th>@lang('messages.table.name')</x-ui.table.th>
                            <x-ui.table.th>@lang('messages.common.type')</x-ui.table.th>
                            <x-ui.table.th>@lang('messages.table.contact')</x-ui.table.th>
                        </tr>
                    </x-ui.table.head>
                    <x-ui.table.body>
                        @foreach ($clients as $client)
                            <x-ui.table.row>
                                <x-ui.table.td class="font-medium">
                                    @if ($client->type?->key === 'person' && $client->person)
                                        {{ trim($client->person->first_name.' '.$client->person->last_name) }}
                                    @else
                                        {{ $client->display_name }}
                                    @endif
                                </x-ui.table.td>
                                <x-ui.table.td>{{ $client->type?->name ?? '-' }}</x-ui.table.td>
                                <x-ui.table.td>
                                    <div>{{ $client->email ?: '-' }}</div>
                                    <div class="text-xs text-zinc-500">{{ $client->phone ?: '-' }}</div>
                                </x-ui.table.td>
                            </x-ui.table.row>
                        @endforeach
                    </x-ui.table.body>
                </x-ui.table>
            @endif
        </flux:card>

        <flux:card class="space-y-4">
            <flux:heading size="lg">@lang('messages.projects.summary_last_6_months')</flux:heading>

            <x-ui.table>
                <x-ui.table.head>
                    <tr>
                        <x-ui.table.th>@lang('messages.common.month')</x-ui.table.th>
                        <x-ui.table.th>@lang('messages.common.amount') (RSD)</x-ui.table.th>
                    </tr>
                </x-ui.table.head>
                <x-ui.table.body>
                    @foreach ($monthlyTotals as $row)
                        <x-ui.table.row>
                            <x-ui.table.td>{{ $row['month'] }}</x-ui.table.td>
                            <x-ui.table.td>{{ number_format($row['total'], 2, ',', '.') }}</x-ui.table.td>
                        </x-ui.table.row>
                    @endforeach
                </x-ui.table.body>
            </x-ui.table>
        </flux:card>
    </div>
</div>
