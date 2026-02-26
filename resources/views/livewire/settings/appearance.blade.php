<section class="w-full">
    @include('partials.settings-heading')

    <flux:heading class="sr-only">{{ __('messages.menu.themeTitle') }}</flux:heading>

    <x-settings.layout :heading="__('messages.menu.theme')" :subheading=" __('messages.menu.themeTitle')">
        <flux:radio.group x-data variant="segmented" x-model="$flux.appearance">
            <flux:radio value="light" icon="sun">{{ __('Light') }}</flux:radio>
            <flux:radio value="dark" icon="moon">{{ __('Dark') }}</flux:radio>
            <flux:radio value="system" icon="computer-desktop">{{ __('System') }}</flux:radio>
        </flux:radio.group>
    </x-settings.layout>
</section>
