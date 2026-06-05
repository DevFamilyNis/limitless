<div class="mx-auto w-full max-w-3xl">
    <div class="mb-6 flex items-center justify-between">
        <div>
            <flux:heading size="xl">{{ $isEditing ? __('messages.lead_campaigns.form_edit_title') : __('messages.lead_campaigns.form_new_title') }}</flux:heading>
            <flux:text>@lang('messages.lead_campaigns.form_subtitle')</flux:text>
        </div>

        <flux:button variant="ghost" :href="route('leads.index')" wire:navigate>
            @lang('messages.buttons.back')
        </flux:button>
    </div>

    @if (session('error'))
        <flux:callout variant="danger" icon="x-circle" class="mb-6">{{ session('error') }}</flux:callout>
    @endif

    <form wire:submit="save" class="space-y-6 rounded-xl border border-zinc-200 p-6 dark:border-zinc-700">
        <flux:input wire:model="name" :label="__('messages.lead_campaigns.name')" required />

        <flux:textarea wire:model="description" :label="__('messages.lead_campaigns.description')" rows="3" />

        <div class="flex items-center gap-3">
            <flux:button variant="primary" type="submit">
                @lang('messages.buttons.save')
            </flux:button>

            @if ($isEditing)
                <flux:button
                    variant="danger"
                    wire:click="deleteCampaign"
                    wire:confirm="@lang('messages.lead_campaigns.delete_confirm')"
                    type="button"
                >
                    @lang('messages.buttons.delete')
                </flux:button>
            @endif
        </div>
    </form>
</div>
