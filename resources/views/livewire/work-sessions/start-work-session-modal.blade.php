<flux:modal name="work-session-start" wire:model="show" :dismissible="false" :closable="false" x-on:cancel.prevent class="max-w-md">
    <div class="space-y-5">
        <div>
            <flux:heading size="lg">Počni radni dan</flux:heading>
            <flux:subheading>Zabeležite početak vašeg radnog dana da biste koristili aplikaciju.</flux:subheading>
        </div>
        <div class="flex justify-end">
            <flux:button wire:click="startSession" variant="primary" wire:loading.attr="disabled">
                Počni radni dan
            </flux:button>
        </div>
    </div>
</flux:modal>
