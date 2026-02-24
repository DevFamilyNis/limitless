<div class="mx-auto w-full max-w-3xl">
    <div class="mb-6 flex items-center justify-between">
        <flux:heading size="xl">{{ $isEditing ? __('messages.settings.issue_categories.edit_title') : __('messages.settings.issue_categories.new_title') }}</flux:heading>
        <flux:button variant="ghost" :href="route('settings.issue-categories.index')" wire:navigate>@lang('messages.actions.back')</flux:button>
    </div>
    <form wire:submit="save" class="space-y-4 rounded-xl border border-zinc-200 p-6 dark:border-zinc-700">
        <flux:input wire:model="name" :label="__('messages.table.name')" required />
        <flux:checkbox wire:model="isActive" :label="__('messages.status_labels.active_f')" />
        <flux:button variant="primary" type="submit">@lang('messages.actions.save')</flux:button>
    </form>
</div>
