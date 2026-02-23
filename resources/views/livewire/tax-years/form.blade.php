<div class="mx-auto w-full max-w-3xl">
    <div class="mb-6 flex items-center justify-between">
        <div>
            <flux:heading size="xl">{{ $isEditing ? 'Izmena poreske godine' : 'Nova poreska godina' }}</flux:heading>
            <flux:text>Unesi godinu i pragove za tu godinu.</flux:text>
        </div>

        <flux:button variant="ghost" :href="route('tax-years.index')" wire:navigate>
            Nazad
        </flux:button>
    </div>

    <form wire:submit="save" class="space-y-6 rounded-xl border border-zinc-200 p-6 dark:border-zinc-700">
        <div class="grid gap-4 md:grid-cols-3">
            <flux:input wire:model="year" label="Godina" type="number" min="2000" max="2100" required />
            <flux:input wire:model="firstThresholdAmount" label="Prvi prag (RSD)" type="number" step="0.01" min="0.01" required />
            <flux:input wire:model="secondThresholdAmount" label="Drugi prag (RSD)" type="number" step="0.01" min="0.01" required />
        </div>

        <div class="flex items-center gap-3">
            <flux:button variant="primary" type="submit">
                Sačuvaj
            </flux:button>
        </div>
    </form>
</div>
