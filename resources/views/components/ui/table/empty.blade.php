@props([
    'colspan' => 1,
])

<tr>
    <td class="px-4 py-6 text-center text-zinc-500" colspan="{{ $colspan }}">
        {{ $slot }}
    </td>
</tr>
