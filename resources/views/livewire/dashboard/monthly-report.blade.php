@php
    $fmt = fn(float $v) => number_format(abs($v), 0, ',', '.') . ' RSD';

    $dirColors = [
        'up'      => 'text-emerald-600 dark:text-emerald-400',
        'down'    => 'text-red-600 dark:text-red-400',
        'neutral' => 'text-zinc-700 dark:text-zinc-300',
    ];
    $pctBg = [
        'up'      => 'bg-emerald-50 text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-300',
        'down'    => 'bg-red-50 text-red-700 dark:bg-red-900/30 dark:text-red-300',
        'neutral' => 'bg-zinc-100 text-zinc-500 dark:bg-zinc-800 dark:text-zinc-400',
    ];
    $arrowUp   = '<path fill-rule="evenodd" d="M10 17.25a.75.75 0 0 1-.75-.75V5.81l-3.22 3.22a.75.75 0 1 1-1.06-1.06l4.5-4.5a.75.75 0 0 1 1.06 0l4.5 4.5a.75.75 0 0 1-1.06 1.06l-3.22-3.22V16.5a.75.75 0 0 1-.75.75Z" clip-rule="evenodd"/>';
    $arrowDown = '<path fill-rule="evenodd" d="M10 2.75a.75.75 0 0 1 .75.75v10.69l3.22-3.22a.75.75 0 1 1 1.06 1.06l-4.5 4.5a.75.75 0 0 1-1.06 0l-4.5-4.5a.75.75 0 1 1 1.06-1.06l3.22 3.22V3.5a.75.75 0 0 1 .75-.75Z" clip-rule="evenodd"/>';
@endphp

<div class="flex h-full min-w-0 flex-col rounded-xl border border-zinc-200 bg-white p-5 dark:border-zinc-700 dark:bg-zinc-900">

    {{-- Header --}}
    <div class="mb-4 flex items-start justify-between">
        <div>
            <h2 class="text-base font-semibold text-zinc-900 dark:text-zinc-100">Mesečni izveštaj</h2>
            <p class="text-xs text-zinc-400 dark:text-zinc-500">{{ $report['period'] }}</p>
        </div>
        <span class="rounded-md bg-zinc-100 px-2 py-0.5 text-xs font-medium text-zinc-500 dark:bg-zinc-800 dark:text-zinc-400">
            vs prosek ove godine
        </span>
    </div>

    {{-- Summary --}}
    <div class="mb-4 rounded-lg bg-zinc-50 px-4 py-3 text-sm leading-relaxed text-zinc-600 dark:bg-zinc-800/50 dark:text-zinc-300">
        {{ $report['summary'] }}
    </div>

    {{-- Key metrics --}}
    <p class="mb-3 text-xs font-semibold uppercase tracking-wider text-zinc-400 dark:text-zinc-500">Ključni rezultati</p>

    <div class="flex flex-1 flex-col justify-between gap-3">
        @foreach ($report['metrics'] as $metric)
            @php
                $dir   = $metric['direction'];
                $pct   = $metric['pct'];
                $val   = $metric['value'];
                $noData = $val === null || ($val == 0 && $pct === null && $metric['format'] === 'currency');
            @endphp

            <div class="flex min-w-0 items-center justify-between gap-3">
                <div class="min-w-0 flex-1">

                    {{-- Label + value row --}}
                    <div class="flex flex-wrap items-center gap-x-2 gap-y-1">
                        <span class="text-sm font-medium text-zinc-500 dark:text-zinc-400">{{ $metric['label'] }}</span>

                        @if ($noData)
                            {{-- No data this month --}}
                            <span class="text-sm font-medium text-zinc-300 dark:text-zinc-600">—</span>
                            <span class="rounded px-1.5 py-0.5 text-xs font-medium bg-zinc-100 text-zinc-400 dark:bg-zinc-800 dark:text-zinc-600">
                                Nema podataka
                            </span>
                        @else
                            {{-- Value --}}
                            <span class="text-sm font-semibold {{ $dirColors[$dir] }}">
                                @if ($metric['format'] === 'percent')
                                    {{ $val !== null ? number_format($val, 1, ',', '.') . '%' : '—' }}
                                @else
                                    {{ $val < 0 ? '−' : '' }}{{ $fmt($val) }}
                                @endif
                            </span>

                            {{-- % badge — only when we have a real comparison --}}
                            @if ($pct !== null)
                                <span class="inline-flex items-center gap-0.5 rounded px-1.5 py-0.5 text-xs font-medium {{ $pctBg[$dir] }}">
                                    @if ($dir === 'up')
                                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" class="size-3">{!! $arrowUp !!}</svg>
                                    @elseif ($dir === 'down')
                                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor" class="size-3">{!! $arrowDown !!}</svg>
                                    @endif
                                    {{ ($pct > 0 ? '+' : '') . number_format($pct, 1, ',', '.') . '%' }}
                                </span>
                            @elseif ($val !== null && $val != 0)
                                {{-- Has value but no historical avg to compare --}}
                                <span class="rounded px-1.5 py-0.5 text-xs font-medium bg-zinc-100 text-zinc-400 dark:bg-zinc-800 dark:text-zinc-500">
                                    Novi podaci
                                </span>
                            @endif
                        @endif
                    </div>

                    {{-- Signal text --}}
                    @if (!$noData)
                        <p class="mt-0.5 truncate text-xs text-zinc-400 dark:text-zinc-500">
                            {{ $metric['signal'] }}
                        </p>
                    @endif
                </div>
            </div>

            @if (!$loop->last)
                <div class="border-t border-zinc-100 dark:border-zinc-800"></div>
            @endif
        @endforeach
    </div>

</div>
