<flux:modal name="work-session-start" wire:model="show" :dismissible="false" :closable="false" x-on:cancel.prevent class="max-w-md">
    <div class="space-y-5">
        @if ($mode === 'start')
            <div>
                <flux:heading size="lg">Počni radni dan</flux:heading>
                <flux:subheading>Zabeležite početak vašeg radnog dana da biste koristili aplikaciju.</flux:subheading>
            </div>
            <div class="flex justify-end">
                <flux:button wire:click="startSession" variant="primary" wire:loading.attr="disabled">
                    Počni radni dan
                </flux:button>
            </div>
        @else
            <div>
                <flux:heading size="lg">Radni dan je aktivan</flux:heading>
                <flux:subheading>Evidentirano je da je radni dan pokrenut na drugom uređaju i još uvek nije završen. Šta želite da uradite?</flux:subheading>
            </div>
            <div class="flex justify-end gap-3">
                <flux:button wire:click="finishSession" wire:loading.attr="disabled">
                    Završi radni dan
                </flux:button>
                <flux:button wire:click="continueSession" variant="primary" wire:loading.attr="disabled">
                    Nastavi
                </flux:button>
            </div>
        @endif
    </div>
</flux:modal>
