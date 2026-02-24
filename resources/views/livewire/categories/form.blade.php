<div class="mx-auto w-full max-w-3xl">
    <div class="mb-6 flex items-center justify-between">
        <div>
            <flux:heading size="xl">{{ $isEditing ? __('messages.categories.edit_title') : __('messages.categories.new_title') }}</flux:heading>
            <flux:text>@lang('messages.categories.form_subtitle')</flux:text>
        </div>

        <flux:button variant="ghost" :href="route('categories.index')" wire:navigate>
            @lang('messages.actions.back')
        </flux:button>
    </div>

    <form wire:submit="save" class="space-y-6 rounded-xl border border-zinc-200 p-6 dark:border-zinc-700">
        <div class="grid gap-4 md:grid-cols-2">
            <flux:select wire:model="categoryTypeId" :label="__('messages.common.type')" required>
                <option value="">@lang('messages.categories.select_type')</option>
                @foreach ($types as $type)
                    <option value="{{ $type->id }}">{{ $type->key === 'expense' ? __('messages.categories.expense') : __('messages.categories.income') }}</option>
                @endforeach
            </flux:select>

            <flux:input wire:model="name" :label="__('messages.table.name')" required />
        </div>

        <div class="flex items-center gap-3">
            <flux:button variant="primary" type="submit">
                @lang('messages.actions.save')
            </flux:button>
        </div>
    </form>
</div>
