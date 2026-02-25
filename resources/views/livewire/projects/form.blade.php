<div class="mx-auto w-full max-w-3xl">
    <div class="mb-6 flex items-center justify-between">
        <div>
            <flux:heading size="xl">{{ $isEditing ? __('messages.projects.edit_title') : __('messages.projects.new_title') }}</flux:heading>
            <flux:text>@lang('messages.form.formTitle')</flux:text>
        </div>

        <flux:button variant="ghost" :href="route('projects.index')" wire:navigate>
            @lang('messages.buttons.back')
        </flux:button>
    </div>

    <form wire:submit="save" class="space-y-6 rounded-xl border border-zinc-200 p-6 dark:border-zinc-700">
        <div class="grid gap-4 md:grid-cols-2">
            <flux:input wire:model="code" :label="__('messages.table.code')" required />
            <flux:input wire:model="name" :label="__('messages.table.name')" required />
        </div>

        <flux:select wire:model="projectColor" label="Boja projekta">
            <option value="">⚪ Automatski (predlog po nazivu projekta)</option>
            @foreach ($projectColorOptions as $key => $label)
                <option value="{{ $key }}">{{ $label }}</option>
            @endforeach
        </flux:select>

        <flux:textarea wire:model="description" :label="__('messages.form.note')" rows="4" />

        <div class="flex items-center gap-3">
            <flux:button variant="primary" type="submit">
                @lang('messages.buttons.save')
            </flux:button>
        </div>
    </form>
</div>
