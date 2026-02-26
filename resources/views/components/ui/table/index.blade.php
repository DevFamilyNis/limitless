@props([
    'compact' => false,
    'rounded' => 'xl',
    'bordered' => true,
    'tableClass' => '',
])

@php
    $radiusClass = match ($rounded) {
        'none' => '',
        'sm' => 'rounded-sm',
        'md' => 'rounded-md',
        'lg' => 'rounded-lg',
        default => 'rounded-xl',
    };
@endphp

<div {{ $attributes->class([$radiusClass, 'overflow-hidden', 'border border-zinc-200 dark:border-zinc-700' => $bordered]) }}>
    <div class="w-full overflow-x-auto">
        <table class="{{ $compact ? 'w-full text-xs' : 'w-full text-sm' }} {{ $tableClass }}">
            {{ $slot }}
        </table>
    </div>
</div>
