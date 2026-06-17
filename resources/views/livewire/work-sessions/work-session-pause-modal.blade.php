<flux:modal name="work-session-pause" wire:model="show" :dismissible="false" :closable="false" x-on:cancel.prevent class="max-w-md">
    <div class="space-y-5">
        <div>
            <flux:heading size="lg">Radni dan je na pauzi</flux:heading>
            <flux:subheading>Vaš radni dan je trenutno pausiran. Kliknite Nastavi kada budete spremni da nastavite sa radom.</flux:subheading>
        </div>
        <div class="flex justify-end">
            <flux:button wire:click="resume" variant="primary" wire:loading.attr="disabled">
                Nastavi
            </flux:button>
        </div>
    </div>
</flux:modal>
