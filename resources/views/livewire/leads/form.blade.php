<div class="mx-auto w-full max-w-3xl">
    <div class="mb-6 flex items-center justify-between">
        <div>
            <flux:heading size="xl">{{ $isEditing ? __('messages.leads.form_edit_title') : __('messages.leads.form_new_title') }}</flux:heading>
            <flux:text>@lang('messages.leads.form_subtitle')</flux:text>
        </div>

        <flux:button variant="ghost" :href="route('leads.index')" wire:navigate>
            @lang('messages.buttons.back')
        </flux:button>
    </div>

    <form wire:submit="save" class="space-y-6 rounded-xl border border-zinc-200 p-6 dark:border-zinc-700">
        <flux:input wire:model="companyName" :label="__('messages.leads.company_name')" required />

        <div class="grid gap-4 md:grid-cols-2">
            <flux:input wire:model="email" :label="__('messages.form.email')" type="email" />
            <flux:input wire:model="phone" :label="__('messages.form.phone')" />
        </div>

        <flux:select wire:model.live="leadStatusId" :label="__('messages.table.status')" required>
            @foreach ($statuses as $status)
                <option value="{{ $status->id }}">{{ $status->name }}</option>
            @endforeach
        </flux:select>

        <div class="flex items-center gap-3">
            <flux:button variant="primary" type="submit">
                @lang('messages.buttons.save')
            </flux:button>
        </div>
    </form>
</div>
