<div class="flex flex-col gap-6">
    <x-auth-header title="Prijava preko linka" description="Unesi email i poslaćemo ti jednokratni link." />

    <x-auth-session-status class="text-center" :status="session('status')" />

    <form wire:submit.prevent="send" class="flex flex-col gap-6">
        <flux:input
            wire:model.defer="email"
            name="email"
            label="Email"
            type="email"
            required
            autofocus
            autocomplete="email"
            placeholder="email@example.com"
        />

        <div class="flex items-center justify-end">
            <flux:button variant="primary" type="submit" class="w-full">
                Pošalji link
            </flux:button>
        </div>
    </form>
</div>
