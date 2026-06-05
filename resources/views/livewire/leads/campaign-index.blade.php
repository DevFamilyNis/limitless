<div class="flex h-full w-full flex-1 flex-col gap-6">
    <div class="flex flex-col gap-4 md:flex-row md:items-end md:justify-between">
        <div>
            <flux:heading size="xl">@lang('messages.lead_campaigns.title')</flux:heading>
            <flux:text>@lang('messages.lead_campaigns.subtitle')</flux:text>
        </div>

        <flux:button variant="primary" :href="route('leads.campaign.create')" wire:navigate>
            @lang('messages.lead_campaigns.add_campaign')
        </flux:button>
    </div>

    @if (session('status'))
        <flux:callout variant="success" icon="check-circle">{{ session('status') }}</flux:callout>
    @endif

    @if (session('error'))
        <flux:callout variant="danger" icon="x-circle">{{ session('error') }}</flux:callout>
    @endif

    <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-3">
        @forelse ($campaigns as $campaign)
            @php($counts = $leadCounts->get($campaign->id, collect()))
            <flux:card class="flex flex-col gap-4">
                <div class="flex items-start justify-between gap-2">
                    <div>
                        <flux:heading size="lg">{{ $campaign->name }}</flux:heading>
                        @if ($campaign->description)
                            <flux:text class="mt-1 text-sm text-zinc-500">{{ $campaign->description }}</flux:text>
                        @endif
                    </div>
                    <flux:button
                        variant="ghost"
                        size="sm"
                        :href="route('leads.campaign.edit', $campaign)"
                        wire:navigate
                    >
                        <x-ui.icons.pen class="size-3.5" />
                    </flux:button>
                </div>

                <div class="grid grid-cols-2 gap-2 sm:grid-cols-3">
                    @foreach ($statuses as $status)
                        <div class="rounded-lg bg-zinc-50 px-3 py-2 dark:bg-zinc-800">
                            <div class="text-xs text-zinc-500">{{ $status->name }}</div>
                            <div class="text-lg font-semibold">
                                {{ $counts->get($status->key)?->total ?? 0 }}
                            </div>
                        </div>
                    @endforeach
                </div>

                <flux:button
                    variant="filled"
                    class="w-full"
                    :href="route('leads.campaign', $campaign)"
                    wire:navigate
                >
                    @lang('messages.lead_campaigns.view_leads')
                </flux:button>
            </flux:card>
        @empty
            <div class="col-span-full text-center text-zinc-400">
                @lang('messages.lead_campaigns.no_campaigns')
            </div>
        @endforelse
    </div>
</div>
