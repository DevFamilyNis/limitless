@props([
    'align' => 'left',
])

@php
    $alignClass = $align === 'right'
        ? 'text-right'
        : ($align === 'center' ? 'text-center' : 'text-left');
@endphp

<th {{ $attributes->class(["px-4 py-3 {$alignClass}"]) }}>
    {{ $slot }}
</th>
