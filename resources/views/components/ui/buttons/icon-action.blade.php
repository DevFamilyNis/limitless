@props([
    'href' => null,
    'title' => '',
    'color' => 'neutral',
    'navigate' => false,
    'size' => 'sm',
])

@php
    $colorClasses = match ($color) {
        'primary' => 'text-blue-600 hover:text-blue-700 dark:text-blue-400 dark:hover:text-blue-300',
        'success' => 'text-emerald-600 hover:text-emerald-700 dark:text-emerald-400 dark:hover:text-emerald-300',
        'warning' => 'text-amber-600 hover:text-amber-700 dark:text-amber-400 dark:hover:text-amber-300',
        'danger' => 'text-red-600 hover:text-red-700 dark:text-red-400 dark:hover:text-red-300',
        default => 'text-zinc-600 hover:text-zinc-900 dark:text-zinc-400 dark:hover:text-zinc-100',
    };
@endphp

@if ($href !== null && $navigate)
    <flux:button
        :href="$href"
        :title="$title"
        :size="$size"
        variant="ghost"
        wire:navigate
        {{ $attributes->class(['size-10 p-1 hover:bg-transparent', $colorClasses]) }}
    >
        {{ $slot }}
    </flux:button>
@endif

@if ($href !== null && ! $navigate)
    <flux:button
        :href="$href"
        :title="$title"
        :size="$size"
        variant="ghost"
        {{ $attributes->class(['size-8 p-0 hover:bg-transparent', $colorClasses]) }}
    >
        {{ $slot }}
    </flux:button>
@endif

@if ($href === null)
    <flux:button
        type="button"
        :title="$title"
        :size="$size"
        variant="ghost"
        {{ $attributes->class(['size-8 p-0 hover:bg-transparent', $colorClasses]) }}
    >
        {{ $slot }}
    </flux:button>
@endif
