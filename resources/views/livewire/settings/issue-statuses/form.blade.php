<div class="mx-auto w-full max-w-3xl">
    <div class="mb-6 flex items-center justify-between">
        <flux:heading size="xl">{{ $isEditing ? 'Izmena statusa' : 'Novi status' }}</flux:heading>
        <flux:button variant="ghost" :href="route('settings.issue-statuses.index')" wire:navigate>Nazad</flux:button>
    </div>
    <form wire:submit="save" class="space-y-4 rounded-xl border border-zinc-200 p-6 dark:border-zinc-700">
        <div class="grid gap-4 md:grid-cols-3">
            <flux:input wire:model="key" label="Key" required />
            <flux:input wire:model="name" label="Naziv" required />
            <flux:input wire:model="sortOrder" type="number" min="0" label="Sort" required />
        </div>
        <flux:checkbox wire:model="isActive" label="Aktivan" />
        <flux:button variant="primary" type="submit">Sačuvaj</flux:button>
    </form>
</div>
