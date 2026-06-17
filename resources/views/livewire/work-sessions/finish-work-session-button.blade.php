<div>
    @if ($status === 'open')
        <flux:menu.separator />
        <flux:menu.item wire:click="pauseSession" icon="pause-circle" wire:loading.attr="disabled">
            Pauza
        </flux:menu.item>
        <flux:menu.item wire:click="finishSession" icon="stop-circle" wire:loading.attr="disabled">
            Završi radni dan
        </flux:menu.item>
    @elseif ($status === 'finished')
        <flux:menu.separator />
        <flux:menu.item icon="check-circle" disabled>
            Radni dan završen
        </flux:menu.item>
    @endif
</div>
