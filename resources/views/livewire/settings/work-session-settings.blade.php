<div class="flex h-full w-full flex-1 flex-col gap-6">
    <div>
        <flux:heading size="xl">Radni dan — podešavanja</flux:heading>
        <flux:text>Konfigurišite podsetnik za radni dan.</flux:text>
    </div>

    @if (session('status'))
        <flux:callout variant="success" icon="check-circle">{{ session('status') }}</flux:callout>
    @endif

    <form wire:submit.prevent="save" class="max-w-sm space-y-4" x-data="{ enabled: @js($reminderEnabled) }">
        <flux:field variant="inline">
            <flux:switch wire:model="reminderEnabled" x-model="enabled" />
            <flux:label>Podsetnik uključen</flux:label>
            <flux:error name="reminderEnabled" />
        </flux:field>

        <div x-show="enabled" x-collapse>
            <flux:field>
                <flux:label>Kašnjenje podsetnika (minuti)</flux:label>
                <flux:description>Koliko minuta nakon početka radnog dana da se prikaže podsetnik.</flux:description>
                <flux:input wire:model="reminderDelayMinutes" type="number" min="15" max="480" />
                <flux:error name="reminderDelayMinutes" />
            </flux:field>
        </div>

        <flux:button type="submit" variant="primary">Sačuvaj</flux:button>
    </form>
</div>
