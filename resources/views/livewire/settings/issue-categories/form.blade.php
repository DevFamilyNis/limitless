<div class="mx-auto w-full max-w-3xl">
    <div class="mb-6 flex items-center justify-between">
        <flux:heading size="xl">{{ $isEditing ? 'Izmena kategorije' : 'Nova kategorija' }}</flux:heading>
        <flux:button variant="ghost" :href="route('settings.issue-categories.index')" wire:navigate>Nazad</flux:button>
    </div>
    <form wire:submit="save" class="space-y-4 rounded-xl border border-zinc-200 p-6 dark:border-zinc-700">
        <flux:input wire:model="name" label="Naziv" required />
        <flux:checkbox wire:model="isActive" label="Aktivna" />
        <flux:button variant="primary" type="submit">Sačuvaj</flux:button>
    </form>
</div>
