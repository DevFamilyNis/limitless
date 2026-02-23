<div class="mx-auto w-full max-w-3xl">
    <div class="mb-6 flex items-center justify-between">
        <div>
            <flux:heading size="xl">{{ $isEditing ? 'Izmena kategorije' : 'Nova kategorija' }}</flux:heading>
            <flux:text>Unesi tip i naziv kategorije.</flux:text>
        </div>

        <flux:button variant="ghost" :href="route('categories.index')" wire:navigate>
            Nazad
        </flux:button>
    </div>

    <form wire:submit="save" class="space-y-6 rounded-xl border border-zinc-200 p-6 dark:border-zinc-700">
        <div class="grid gap-4 md:grid-cols-2">
            <flux:select wire:model="categoryTypeId" label="Tip" required>
                <option value="">Izaberi tip</option>
                @foreach ($types as $type)
                    <option value="{{ $type->id }}">{{ $type->key === 'expense' ? 'Rashod' : $type->name }}</option>
                @endforeach
            </flux:select>

            <flux:input wire:model="name" label="Naziv" required />
        </div>

        <div class="flex items-center gap-3">
            <flux:button variant="primary" type="submit">
                Sačuvaj
            </flux:button>
        </div>
    </form>
</div>
