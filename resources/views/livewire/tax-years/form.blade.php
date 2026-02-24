<div class="mx-auto w-full max-w-3xl">
    <div class="mb-6 flex items-center justify-between">
        <div>
            <flux:heading size="xl">{{ $isEditing ? __('messages.tax_years.edit_title') : __('messages.tax_years.new_title') }}</flux:heading>
            <flux:text>@lang('messages.tax_years.form_subtitle')</flux:text>
        </div>

        <flux:button variant="ghost" :href="route('tax-years.index')" wire:navigate>
            @lang('messages.actions.back')
        </flux:button>
    </div>

    <form wire:submit="save" class="space-y-6 rounded-xl border border-zinc-200 p-6 dark:border-zinc-700">
        <div class="grid gap-4 md:grid-cols-3">
            <flux:input wire:model="year" :label="__('messages.common.year')" type="number" min="2000" max="2100" required />
            <flux:input wire:model="firstThresholdAmount" :label="__('messages.tax_years.first_threshold')" type="number" step="0.01" min="0.01" required />
            <flux:input wire:model="secondThresholdAmount" :label="__('messages.tax_years.second_threshold')" type="number" step="0.01" min="0.01" required />
        </div>

        <div class="flex items-center gap-3">
            <flux:button variant="primary" type="submit">
                @lang('messages.actions.save')
            </flux:button>
        </div>
    </form>
</div>
