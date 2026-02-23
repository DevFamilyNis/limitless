@props([
    'highlight' => false,
])

<tr {{ $attributes->class([
    'border-t border-zinc-200 dark:border-zinc-700',
    'bg-red-50/60 dark:bg-red-950/20' => $highlight,
]) }}>
    {{ $slot }}
</tr>
