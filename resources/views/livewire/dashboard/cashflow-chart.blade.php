@php
    use Illuminate\Support\Number;

    $fmt = fn(float $v) => number_format($v, 2, ',', '.') . ' RSD';
    $signal = $data['signal'] ?? null;
    $totals = $data['totals'] ?? null;
    $months = $data['months'] ?? [];

    $signalColors = [
        'safe'    => ['bg' => 'bg-emerald-100 dark:bg-emerald-900/30', 'text' => 'text-emerald-700 dark:text-emerald-300', 'dot' => 'bg-emerald-500'],
        'caution' => ['bg' => 'bg-amber-100 dark:bg-amber-900/30',    'text' => 'text-amber-700 dark:text-amber-300',       'dot' => 'bg-amber-500'],
        'unsafe'  => ['bg' => 'bg-red-100 dark:bg-red-900/30',        'text' => 'text-red-700 dark:text-red-300',           'dot' => 'bg-red-500'],
    ];
    $sc = $signalColors[$signal['status'] ?? 'unsafe'] ?? $signalColors['unsafe'];
@endphp

<div class="rounded-xl border border-zinc-200 bg-white p-5 dark:border-zinc-700 dark:bg-zinc-900">

    {{-- Header --}}
    <div class="mb-5 flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
        <div>
            <h2 class="text-lg font-semibold text-zinc-900 dark:text-zinc-100">Prihodi i rashodi</h2>
            <p class="text-sm text-zinc-500 dark:text-zinc-400">Pregled novčanog toka za izabranu kalendarsku godinu</p>
        </div>

        <div class="flex flex-wrap items-center gap-3">
            {{-- Investment signal badge --}}
            @if ($signal)
                <div class="flex items-center gap-2 rounded-md px-3 py-1.5 text-sm font-medium {{ $sc['bg'] }} {{
                $sc['text'] }}">
                    <span class="size-2 rounded-full {{ $sc['dot'] }}"></span>
                    {{ $signal['label'] }}
                </div>
                @if ($availableYears !== [])
                    <a
                        href="{{ route('dashboard.investment-signal', ['year' => $selectedYear]) }}"
                        wire:navigate
                        class="inline-flex items-center gap-1.5 rounded-md bg-zinc-900 px-3 py-1.5 text-sm font-medium text-white transition hover:bg-zinc-700 dark:bg-zinc-100 dark:text-zinc-900 dark:hover:bg-zinc-300"
                    >
                        <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.75" stroke-linecap="round" stroke-linejoin="round" class="size-4">
                            <path d="M2.25 18 9 11.25l4.306 4.306a11.95 11.95 0 0 1 5.814-5.518l2.74-1.22m0 0-5.94-2.281m5.94 2.28-2.28 5.941"/>
                        </svg>
                        Detalji
                    </a>
                @endif
            @endif

            {{-- Year select --}}
            @if ($availableYears !== [])
                <select
                    wire:model.live="selectedYear"
                    class="rounded-md border border-zinc-300 bg-white px-3 py-1.5 text-sm text-zinc-700 shadow-sm focus:border-blue-500 focus:outline-none dark:border-zinc-600 dark:bg-zinc-800 dark:text-zinc-200"
                >
                    @foreach ($availableYears as $yr)
                        <option value="{{ $yr }}">{{ $yr }}</option>
                    @endforeach
                </select>
            @endif
        </div>
    </div>

    {{-- Empty state --}}
    @if ($availableYears === [])
        <div class="flex flex-col items-center justify-center rounded-lg border border-dashed border-zinc-300 py-16 text-center dark:border-zinc-700">
            <svg class="mb-3 size-10 text-zinc-300 dark:text-zinc-600" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M3 3v1.5M3 19.5V21M21 3v1.5M21 19.5V21M8.25 6.75h7.5M7.5 21h9a2.25 2.25 0 0 0 2.25-2.25V5.25A2.25 2.25 0 0 0 16.5 3h-9A2.25 2.25 0 0 0 5.25 5.25v13.5A2.25 2.25 0 0 0 7.5 21ZM8.25 10.5h7.5M8.25 14.25h4.5" />
            </svg>
            <p class="text-sm font-medium text-zinc-500 dark:text-zinc-400">Nema podataka</p>
            <p class="mt-1 text-xs text-zinc-400 dark:text-zinc-500">Evidentirajte prihode i rashode da biste videli grafikon.</p>
        </div>

    @else
        {{-- SVG Area Chart — viewBox aspect ratio (840:160) controls responsive height automatically --}}
        <div class="overflow-x-auto">
            <svg
                viewBox="0 0 {{ $svg['vbW'] }} {{ $svg['vbH'] }}"
                class="w-full min-w-[400px]"
                aria-label="Grafikon prihoda i rashoda"
                role="img"
            >
                <defs>
                    <linearGradient id="incomeGrad" x1="0" y1="0" x2="0" y2="1">
                        <stop offset="0%" stop-color="#3b82f6" stop-opacity="0.25"/>
                        <stop offset="100%" stop-color="#3b82f6" stop-opacity="0.02"/>
                    </linearGradient>
                    <linearGradient id="expenseGrad" x1="0" y1="0" x2="0" y2="1">
                        <stop offset="0%" stop-color="#ef4444" stop-opacity="0.18"/>
                        <stop offset="100%" stop-color="#ef4444" stop-opacity="0.02"/>
                    </linearGradient>
                </defs>

                {{-- Horizontal grid lines & y-axis labels --}}
                @foreach ($svg['yLabels'] as $lbl)
                    <line
                        x1="{{ $svg['padL'] }}" y1="{{ $lbl['y'] }}"
                        x2="{{ $svg['vbW'] - 20 }}" y2="{{ $lbl['y'] }}"
                        stroke="currentColor" stroke-opacity="0.08" stroke-width="1"
                    />
                    <text
                        x="{{ $svg['padL'] - 6 }}" y="{{ $lbl['y'] }}"
                        text-anchor="end" dominant-baseline="middle"
                        class="fill-zinc-400 dark:fill-zinc-500"
                        font-size="11"
                    >{{ $lbl['label'] }}</text>
                @endforeach

                {{-- Income area fill --}}
                <path d="{{ $svg['incomeArea'] }}" fill="url(#incomeGrad)"/>

                {{-- Expense area fill --}}
                <path d="{{ $svg['expenseArea'] }}" fill="url(#expenseGrad)"/>

                {{-- Income line --}}
                <polyline
                    points="{{ $svg['incomeLine'] }}"
                    fill="none"
                    stroke="#3b82f6"
                    stroke-width="1.5"
                    stroke-linejoin="round"
                    stroke-linecap="round"
                />

                {{-- Expense line --}}
                <polyline
                    points="{{ $svg['expenseLine'] }}"
                    fill="none"
                    stroke="#ef4444"
                    stroke-width="1.5"
                    stroke-linejoin="round"
                    stroke-linecap="round"
                />

                {{-- Data points & x-axis labels --}}
                @foreach ($svg['months'] as $i => $m)
                    <circle cx="{{ $svg['xs'][$i] }}" cy="{{ $svg['yIncome'][$i] }}" r="2" fill="#3b82f6"/>
                    <circle cx="{{ $svg['xs'][$i] }}" cy="{{ $svg['yExpense'][$i] }}" r="2" fill="#ef4444"/>

                    <text
                        x="{{ $svg['xs'][$i] }}" y="{{ $svg['padT'] + $svg['innerH'] + 22 }}"
                        text-anchor="middle"
                        class="fill-zinc-400 dark:fill-zinc-500"
                        font-size="11"
                    >{{ $m['label'] }}</text>
                @endforeach
            </svg>
        </div>

        {{-- Legend --}}
        <div class="mt-3 flex flex-wrap items-center gap-5 text-sm text-zinc-600 dark:text-zinc-400">
            <div class="flex items-center gap-1.5">
                <span class="inline-block size-3 rounded-full bg-blue-500"></span>
                Prihod
            </div>
            <div class="flex items-center gap-1.5">
                <span class="inline-block size-3 rounded-full bg-red-500"></span>
                Rashod
            </div>
            <div class="flex items-center gap-1.5">
                <span class="inline-block size-3 rounded-full {{ $totals['net'] >= 0 ? 'bg-emerald-500' : 'bg-zinc-400' }}"></span>
                Neto
            </div>
        </div>

        {{-- Footer totals --}}
        <div class="mt-5 grid grid-cols-3 gap-3 border-t border-zinc-100 pt-5 dark:border-zinc-800">
            <div>
                <p class="text-xs font-medium uppercase tracking-wide text-zinc-400 dark:text-zinc-500">Ukupan prihod</p>
                <p class="mt-1 text-base font-semibold text-blue-600 dark:text-blue-400">{{ $fmt($totals['income']) }}</p>
            </div>
            <div>
                <p class="text-xs font-medium uppercase tracking-wide text-zinc-400 dark:text-zinc-500">Ukupan rashod</p>
                <p class="mt-1 text-base font-semibold text-red-600 dark:text-red-400">{{ $fmt($totals['expense']) }}</p>
            </div>
            <div>
                <p class="text-xs font-medium uppercase tracking-wide text-zinc-400 dark:text-zinc-500">Neto rezultat</p>
                <p class="mt-1 text-base font-semibold {{ $totals['net'] >= 0 ? 'text-emerald-600 dark:text-emerald-400' : 'text-red-600 dark:text-red-400' }}">
                    {{ $fmt($totals['net']) }}
                </p>
            </div>
        </div>

        {{-- Signal explanation --}}
        @if ($signal && $signal['status'] !== 'unsafe' && $signal['recommended_max_investment'] > 0)
            <div class="mt-4 rounded-lg {{ $sc['bg'] }} px-4 py-3 text-sm {{ $sc['text'] }}">
                <span class="font-medium">Preporučena max. investicija:</span>
                {{ $fmt($signal['recommended_max_investment']) }} —
                <span class="opacity-80">{{ $signal['reason'] }}</span>
            </div>
        @elseif ($signal && $signal['status'] === 'unsafe')
            <div class="mt-4 rounded-lg {{ $sc['bg'] }} px-4 py-3 text-sm {{ $sc['text'] }}">
                {{ $signal['reason'] }}
            </div>
        @endif
    @endif

</div>
