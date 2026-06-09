<flux:modal name="work-session-reminder" wire:model="show" :dismissible="false" :closable="false" x-on:cancel.prevent class="max-w-md">
    <div class="space-y-5">
        <div>
            <flux:heading size="lg">Podsetnik za radni dan</flux:heading>
            <flux:subheading>Prošlo je neko vreme od početka radnog dana. Da li i dalje radite?</flux:subheading>
        </div>
        <div class="flex justify-end">
            <flux:button wire:click="acknowledge" variant="primary" wire:loading.attr="disabled">
                Da, nastavljam
            </flux:button>
        </div>
    </div>
</flux:modal>
