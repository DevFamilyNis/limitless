<div class="mx-auto w-full max-w-3xl">
    <div class="mb-6 flex items-center justify-between">
        <div>
            <flux:heading size="xl">{{ $isEditing ? 'Izmena klijenta' : 'Novi klijent' }}</flux:heading>
            <flux:text>Unesi osnovne podatke i sačuvaj klijenta.</flux:text>
        </div>

        <flux:button variant="ghost" :href="route('clients.index')" wire:navigate>
            Nazad na listu
        </flux:button>
    </div>

    <form wire:submit="save" class="space-y-6 rounded-xl border border-zinc-200 p-6 dark:border-zinc-700">
        <flux:select wire:model.live="clientTypeId" label="Tip" required>
            @foreach ($clientTypes as $type)
                <option value="{{ $type->id }}">{{ $type->name }}</option>
            @endforeach
        </flux:select>

        <flux:input wire:model="displayName" label="Display name" required />

        <div class="grid gap-4 md:grid-cols-2">
            <flux:input wire:model="email" label="Email" type="email" />
            <flux:input wire:model="phone" label="Telefon" />
        </div>

        <flux:input wire:model="address" label="Adresa" />
        <flux:textarea wire:model="note" label="Napomena" rows="3" />

        @if ($isCompany)
            <div class="grid gap-4 md:grid-cols-3">
                <flux:input wire:model="pib" label="PIB" required />
                <flux:input wire:model="mb" label="Matični broj" required />
                <flux:input wire:model="bankAccount" label="Račun" required />
            </div>
        @endif

        <div class="flex items-center gap-3">
            <flux:button variant="primary" type="submit">
                Sačuvaj
            </flux:button>
        </div>
    </form>
</div>
