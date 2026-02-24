<div class="mx-auto w-full max-w-3xl">
    <div class="mb-6 flex items-center justify-between">
        <flux:heading size="xl">{{ $isEditing ? __('messages.settings.issue_statuses.edit_title') : __('messages.settings.issue_statuses.new_title') }}</flux:heading>
        <flux:button variant="ghost" :href="route('settings.issue-statuses.index')" wire:navigate>@lang('messages.actions.back')</flux:button>
    </div>
    <form wire:submit="save" class="space-y-4 rounded-xl border border-zinc-200 p-6 dark:border-zinc-700">
        <div class="grid gap-4 md:grid-cols-3">
            <flux:input wire:model="key" :label="__('messages.common.key')" required />
            <flux:input wire:model="name" :label="__('messages.table.name')" required />
            <flux:input wire:model="sortOrder" type="number" min="0" :label="__('messages.common.sort')" required />
        </div>
        <flux:checkbox wire:model="isActive" :label="__('messages.common.active')" />
        <flux:button variant="primary" type="submit">@lang('messages.actions.save')</flux:button>
    </form>
</div>
