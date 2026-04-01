<div class="flex w-full flex-1 flex-col gap-6">
    @php($nextFollowUp = $lead->current_next_follow_up_at)

    <div class="flex flex-col gap-4 md:flex-row md:items-start md:justify-between">
        <div>
            <flux:heading size="xl">{{ $lead->company_name }}</flux:heading>
            <flux:text>@lang('messages.leads.details_subtitle')</flux:text>
        </div>

        <div class="flex items-center gap-2">
            <flux:button variant="ghost" :href="route('leads.index')" wire:navigate>
                @lang('messages.buttons.back')
            </flux:button>
            <flux:button variant="primary" :href="route('leads.edit', $lead)" wire:navigate>
                @lang('messages.buttons.edit')
            </flux:button>
        </div>
    </div>

    <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
        <flux:card>
            <flux:text class="text-xs text-zinc-500">@lang('messages.table.status')</flux:text>
            <flux:heading size="lg">{{ $lead->status?->name ?? '-' }}</flux:heading>
        </flux:card>
        <flux:card>
            <flux:text class="text-xs text-zinc-500">@lang('messages.leads.last_contact')</flux:text>
            <flux:heading size="lg">{{ $lead->last_contacted_at?->format('d.m.Y H:i') ?: '-' }}</flux:heading>
        </flux:card>
        <flux:card>
            <flux:text class="text-xs text-zinc-500">@lang('messages.leads.next_contact')</flux:text>
            @if ($nextFollowUp)
                <flux:heading size="lg" class="text-amber-700 dark:text-amber-300">{{ $nextFollowUp->format('d.m.Y H:i') }}</flux:heading>
                <flux:text class="text-amber-700/80 dark:text-amber-300/80">
                    {{ $nextFollowUp->isPast() ? __('messages.leads.overdue_next_contact') : __('messages.leads.scheduled_next_contact') }}
                </flux:text>
            @else
                <flux:heading size="lg">-</flux:heading>
            @endif
        </flux:card>
        <flux:card>
            <flux:text class="text-xs text-zinc-500">@lang('messages.leads.comments')</flux:text>
            <flux:heading size="lg">{{ $lead->comments->count() }}</flux:heading>
        </flux:card>
    </div>

    <div class="grid gap-6 xl:grid-cols-[minmax(0,1.6fr)_minmax(340px,0.8fr)]">
        <flux:card class="space-y-4">
            <flux:heading size="lg">@lang('messages.leads.history')</flux:heading>

            <div class="space-y-4">
                @forelse ($lead->comments as $comment)
                    <div wire:key="lead-comment-{{ $comment->id }}" class="rounded-xl border border-zinc-200 p-4 dark:border-zinc-700">
                        <div class="flex flex-col gap-2 md:flex-row md:items-center md:justify-between">
                            <div class="flex flex-wrap items-center gap-2">
                                <x-ui.badge color="sky">{{ $comment->status?->name ?? '-' }}</x-ui.badge>
                                <x-ui.badge>{{ strtoupper($comment->event_type) }}</x-ui.badge>
                                @if ($comment->outcome)
                                    <x-ui.badge color="lime">{{ $comment->outcome }}</x-ui.badge>
                                @endif
                            </div>
                            <flux:text class="text-xs text-zinc-500">
                                {{ $comment->author?->name ?? '-' }} · {{ $comment->created_at?->format('d.m.Y H:i') }}
                            </flux:text>
                        </div>

                        <div class="mt-3 whitespace-pre-line text-sm text-zinc-700 dark:text-zinc-200">{{ $comment->body }}</div>

                        <div class="mt-3 grid gap-2 text-xs text-zinc-500 md:grid-cols-4">
                            <div>@lang('messages.leads.contact_method'): {{ $comment->contact_method ?: '-' }}</div>
                            <div>@lang('messages.leads.contacted_at'): {{ $comment->contacted_at?->format('d.m.Y H:i') ?: '-' }}</div>
                            <div>@lang('messages.leads.responded_at'): {{ $comment->responded_at?->format('d.m.Y H:i') ?: '-' }}</div>
                            <div class="font-medium text-amber-700 dark:text-amber-300">@lang('messages.leads.next_contact'): {{ $comment->next_follow_up_at?->format('d.m.Y H:i') ?: '-' }}</div>
                        </div>
                    </div>
                @empty
                    <flux:text class="text-zinc-500">@lang('messages.leads.no_comments')</flux:text>
                @endforelse
            </div>
        </flux:card>

        <div class="grid gap-6 xl:content-start">
            <flux:card class="space-y-3">
                <flux:heading size="lg">@lang('messages.leads.main_info')</flux:heading>
                <div class="grid gap-3 text-sm">
                    <div><span class="text-zinc-500">@lang('messages.leads.company_name'):</span> {{ $lead->company_name }}</div>
                    <div><span class="text-zinc-500">@lang('messages.form.email'):</span> {{ $lead->email ?: '-' }}</div>
                    <div><span class="text-zinc-500">@lang('messages.form.phone'):</span> {{ $lead->phone ?: '-' }}</div>
                    <div><span class="text-zinc-500">@lang('messages.leads.last_contact_method'):</span> {{ $lead->last_contact_method ?: '-' }}</div>
                    <div><span class="text-zinc-500">@lang('messages.leads.next_contact'):</span> <span class="font-medium text-amber-700 dark:text-amber-300">{{ $nextFollowUp?->format('d.m.Y H:i') ?: '-' }}</span></div>
                </div>
            </flux:card>

            <flux:card class="space-y-4">
                <flux:heading size="lg">@lang('messages.actions.add_comment')</flux:heading>

                <form wire:submit="addComment" class="space-y-4">
                    <input type="hidden" wire:model="commentEventType">

                    <flux:select wire:model="commentLeadStatusId" :label="__('messages.table.status')" required>
                        @foreach ($statuses as $status)
                            <option value="{{ $status->id }}">{{ $status->name }}</option>
                        @endforeach
                    </flux:select>

                    <div class="grid gap-4 sm:grid-cols-2">
                        <flux:select wire:model="commentContactMethod" :label="__('messages.leads.contact_method')">
                            <option value="phone">@lang('messages.leads.phone_method')</option>
                            <option value="email">@lang('messages.leads.email_method')</option>
                        </flux:select>
                        <flux:input wire:model="commentContactedAt" :label="__('messages.leads.contacted_at')" type="datetime-local" />
                    </div>

                    <div class="grid gap-4 sm:grid-cols-2">
                        <flux:input wire:model="commentRespondedAt" :label="__('messages.leads.responded_at')" type="datetime-local" />
                        <flux:input wire:model="commentNextFollowUpAt" :label="__('messages.leads.next_follow_up_at')" type="datetime-local" />
                    </div>

                    <flux:textarea wire:model="commentBody" :label="__('messages.form.note')" rows="5" required />

                    <flux:button variant="primary" type="submit">
                        @lang('messages.actions.add_comment')
                    </flux:button>
                </form>
            </flux:card>
        </div>
    </div>
</div>
