@php
    $fmt = fn(float $v) => number_format(abs($v), 0, ',', '.') . ' RSD';
    $fmtPct = fn(?float $p) => $p === null ? null : ($p > 0 ? '+' : '') . number_format($p, 1, ',', '.') . '%';

    $dirColors = [
        'up'      => 'text-emerald-600 dark:text-emerald-400',
        'down'    => 'text-red-600 dark:text-red-400',
        'neutral' => 'text-zinc-500 dark:text-zinc-400',
    ];
    $pctBg = [
        'up'      => 'bg-emerald-50 text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-300',
        'down'    => 'bg-red-50 text-red-700 dark:bg-red-900/30 dark:text-red-300',
        'neutral' => 'bg-zinc-100 text-zinc-600 dark:bg-zinc-800 dark:text-zinc-400',
    ];
    $arrows = ['up' => '↑', 'down' => '↓', 'neutral' => '→'];
@endphp

<div class="flex h-full flex-col rounded-xl border border-zinc-200 bg-white p-5 dark:border-zinc-700 dark:bg-zinc-900">

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
                $dir = $metric['direction'];
                $pctStr = $fmtPct($metric['pct']);
            @endphp
            <div class="flex items-center justify-between gap-3">
                <div class="min-w-0 flex-1">
                    <div class="flex items-center gap-2">
                        <span class="text-sm font-medium text-zinc-700 dark:text-zinc-300">{{ $metric['label'] }}</span>

                        {{-- Value --}}
                        <span class="text-sm font-semibold {{ $dirColors[$dir] }}">
                            @if ($metric['format'] === 'percent')
                                {{ $metric['value'] !== null ? number_format($metric['value'], 1, ',', '.') . '%' : 'N/A' }}
                            @else
                                {{ $metric['value'] >= 0 ? '' : '−' }}{{ $fmt($metric['value']) }}
                            @endif
                        </span>

                        {{-- % badge --}}
                        @if ($pctStr !== null)
                            <span class="rounded px-1.5 py-0.5 text-xs font-medium {{ $pctBg[$dir] }}">
                                {{ $arrows[$dir] }} {{ $pctStr }}
                            </span>
                        @endif
                    </div>
                    <p class="mt-0.5 truncate text-xs text-zinc-400 dark:text-zinc-500">
                        {{ $metric['signal'] }}
                    </p>
                </div>
            </div>

            @if (!$loop->last)
                <div class="border-t border-zinc-100 dark:border-zinc-800"></div>
            @endif
        @endforeach
    </div>

</div>
