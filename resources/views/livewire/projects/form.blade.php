<div class="mx-auto w-full max-w-3xl">
    <div class="mb-6 flex items-center justify-between">
        <div>
            <flux:heading size="xl">{{ $isEditing ? 'Izmena projekta' : 'Novi projekat' }}</flux:heading>
            <flux:text>@lang('messages.form.editTitle')</flux:text>
        </div>

        <flux:button variant="ghost" :href="route('projects.index')" wire:navigate>
            @lang('messages.buttons.back')
        </flux:button>
    </div>

    <form wire:submit="save" class="space-y-6 rounded-xl border border-zinc-200 p-6 dark:border-zinc-700">
        <div class="grid gap-4 md:grid-cols-2">
            <flux:input wire:model="code" label="Kod" required />
            <flux:input wire:model="name" label="Naziv" required />
        </div>

        <flux:textarea wire:model="description" label="Opis" rows="4" />

        <div class="flex items-center gap-3">
            <flux:button variant="primary" type="submit">
                @lang('messages.buttons.save')
            </flux:button>
        </div>
    </form>
</div>
