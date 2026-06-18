<div class="mx-auto w-full max-w-3xl">
    <div class="mb-6 flex items-center justify-between">
        <div>
            <flux:heading size="xl">{{ $isEditing ? __('messages.leads.form_edit_title') : __('messages.leads.form_new_title') }}</flux:heading>
            <flux:text>@lang('messages.leads.form_subtitle')</flux:text>
        </div>

        <flux:button variant="ghost" :href="route('leads.campaign', $campaign)" wire:navigate>
            @lang('messages.buttons.back')
        </flux:button>
    </div>

    <form wire:submit="save" class="space-y-6 rounded-xl border border-zinc-200 p-6 dark:border-zinc-700">
        <flux:input wire:model="companyName" :label="__('messages.leads.company_name')" required />

        <div class="grid gap-4 md:grid-cols-2">
            <flux:input wire:model="email" :label="__('messages.form.email')" type="email" />
            <flux:input wire:model="phone" :label="__('messages.form.phone')" />
        </div>

        <flux:select wire:model="leadCampaignId" :label="__('messages.leads.select_campaign')" required>
            @foreach ($campaigns as $c)
                <option value="{{ $c->id }}">{{ $c->name }}</option>
            @endforeach
        </flux:select>

        <flux:select wire:model.live="leadStatusId" :label="__('messages.table.status')" required>
            @foreach ($statuses as $status)
                <option value="{{ $status->id }}">{{ $status->name }}</option>
            @endforeach
        </flux:select>

        @if (!$isEditing)
            <hr class="border-zinc-200 dark:border-zinc-700">

            <flux:heading size="md">@lang('messages.actions.add_comment') <span class="text-sm font-normal text-zinc-400">(@lang('messages.common.optional'))</span></flux:heading>

            <flux:textarea wire:model="commentBody" :label="__('messages.form.note')" rows="4" />

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
        @endif

        <div class="flex items-center gap-3">
            <flux:button variant="primary" type="submit">
                @lang('messages.buttons.save')
            </flux:button>
        </div>
    </form>
</div>
