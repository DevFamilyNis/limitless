@php
    $fmt = fn(float $v) => number_format($v, 2, ',', '.') . ' RSD';
    $signal = $data['signal'];
    $totals = $data['totals'];
    $months = $data['months'];

    $signalConfig = [
        'safe'    => ['color' => 'emerald', 'icon' => 'check-circle',        'bg' => 'bg-emerald-50 dark:bg-emerald-900/20', 'border' => 'border-emerald-200 dark:border-emerald-800', 'text' => 'text-emerald-700 dark:text-emerald-300'],
        'caution' => ['color' => 'amber',   'icon' => 'exclamation-triangle', 'bg' => 'bg-amber-50 dark:bg-amber-900/20',     'border' => 'border-amber-200 dark:border-amber-800',     'text' => 'text-amber-700 dark:text-amber-300'],
        'unsafe'  => ['color' => 'red',     'icon' => 'x-circle',            'bg' => 'bg-red-50 dark:bg-red-900/20',         'border' => 'border-red-200 dark:border-red-800',         'text' => 'text-red-700 dark:text-red-300'],
    ];
    $sc = $signalConfig[$signal['status']];
@endphp

<div class="flex h-full w-full flex-1 flex-col gap-6">

    {{-- Header --}}
    <div class="flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h1 class="text-xl font-semibold text-zinc-900 dark:text-zinc-100">Detalji investicionog signala</h1>
            <p class="text-sm text-zinc-500 dark:text-zinc-400">Analiza finansijskog toka za godinu {{ $year }}</p>
        </div>

        @if ($years !== [])
            <select
                wire:model.live="year"
                class="rounded-md border border-zinc-300 bg-white px-3 py-1.5 text-sm text-zinc-700 shadow-sm focus:border-blue-500 focus:outline-none dark:border-zinc-600 dark:bg-zinc-800 dark:text-zinc-200"
            >
                @foreach ($years as $yr)
                    <option value="{{ $yr }}">{{ $yr }}</option>
                @endforeach
            </select>
        @endif
    </div>

    {{-- Signal card --}}
    <div class="rounded-xl border p-6 {{ $sc['bg'] }} {{ $sc['border'] }}">
        <div class="flex items-start gap-4">
            <div class="flex size-10 shrink-0 items-center justify-center rounded-full bg-white dark:bg-zinc-900">
                <flux:icon :icon="$sc['icon']" class="size-6 {{ $sc['text'] }}" variant="outline" />
            </div>
            <div class="flex-1">
                <h2 class="text-lg font-semibold {{ $sc['text'] }}">{{ $signal['label'] }}</h2>
                <p class="mt-1 text-sm {{ $sc['text'] }} opacity-90">{{ $signal['reason'] }}</p>

                @if ($signal['recommended_max_investment'] > 0)
                    <div class="mt-4 rounded-lg bg-white/60 px-4 py-3 dark:bg-black/20">
                        <p class="text-xs font-medium uppercase tracking-wide {{ $sc['text'] }} opacity-70">Preporučena maksimalna investicija</p>
                        <p class="mt-1 text-2xl font-bold {{ $sc['text'] }}">{{ $fmt($signal['recommended_max_investment']) }}</p>
                        <p class="mt-1 text-xs {{ $sc['text'] }} opacity-70">
                            Ovo je procena zasnovana na istorijskim podacima, ne finansijski savet.
                        </p>
                    </div>
                @endif
            </div>
        </div>
    </div>

    {{-- Annual totals --}}
    <div class="grid grid-cols-3 gap-4">
        <div class="rounded-xl border border-zinc-200 bg-white p-5 dark:border-zinc-700 dark:bg-zinc-900">
            <p class="text-xs font-medium uppercase tracking-wide text-zinc-400">Ukupan prihod</p>
            <p class="mt-2 text-xl font-semibold text-blue-600 dark:text-blue-400">{{ $fmt($totals['income']) }}</p>
        </div>
        <div class="rounded-xl border border-zinc-200 bg-white p-5 dark:border-zinc-700 dark:bg-zinc-900">
            <p class="text-xs font-medium uppercase tracking-wide text-zinc-400">Ukupan rashod</p>
            <p class="mt-2 text-xl font-semibold text-red-600 dark:text-red-400">{{ $fmt($totals['expense']) }}</p>
        </div>
        <div class="rounded-xl border border-zinc-200 bg-white p-5 dark:border-zinc-700 dark:bg-zinc-900">
            <p class="text-xs font-medium uppercase tracking-wide text-zinc-400">Neto rezultat</p>
            <p class="mt-2 text-xl font-semibold {{ $totals['net'] >= 0 ? 'text-emerald-600 dark:text-emerald-400' : 'text-red-600 dark:text-red-400' }}">
                {{ $fmt($totals['net']) }}
            </p>
        </div>
    </div>

    {{-- Month-by-month table --}}
    <div class="rounded-xl bg-white  dark:bg-zinc-900">
{{--        <div class="border-b border-zinc-100 px-5 py-4 dark:border-zinc-800">--}}
{{--            <h3 class="font-semibold text-zinc-900 dark:text-zinc-100">Mesečni pregled</h3>--}}
{{--        </div>--}}
        <x-ui.table>
            <x-ui.table.head>
                <tr>
                    <x-ui.table.th>Mesec</x-ui.table.th>
                    <x-ui.table.th>Prihod</x-ui.table.th>
                    <x-ui.table.th>Rashod</x-ui.table.th>
                    <x-ui.table.th>Neto</x-ui.table.th>
                    <x-ui.table.th>Status</x-ui.table.th>
                </tr>
            </x-ui.table.head>
            <x-ui.table.body>
                @foreach ($months as $m)
                    @php
                        $hasData = $m['income'] > 0 || $m['expense'] > 0;
                        $isPositive = $m['net'] > 0;
                    @endphp
                    <x-ui.table.row>
                        <x-ui.table.td>
                            <span class="font-medium {{ !$hasData ? 'text-zinc-400 dark:text-zinc-600' : '' }}">
                                {{ $m['label'] }}
                            </span>
                        </x-ui.table.td>
                        <x-ui.table.td>
                            <span class="{{ $hasData ? 'text-blue-600 dark:text-blue-400' : 'text-zinc-300 dark:text-zinc-700' }}">
                                {{ $hasData ? $fmt($m['income']) : '—' }}
                            </span>
                        </x-ui.table.td>
                        <x-ui.table.td>
                            <span class="{{ $hasData ? 'text-red-600 dark:text-red-400' : 'text-zinc-300 dark:text-zinc-700' }}">
                                {{ $hasData ? $fmt($m['expense']) : '—' }}
                            </span>
                        </x-ui.table.td>
                        <x-ui.table.td>
                            @if ($hasData)
                                <span class="font-medium {{ $isPositive ? 'text-emerald-600 dark:text-emerald-400' : 'text-red-600 dark:text-red-400' }}">
                                    {{ $fmt($m['net']) }}
                                </span>
                            @else
                                <span class="text-zinc-300 dark:text-zinc-700">—</span>
                            @endif
                        </x-ui.table.td>
                        <x-ui.table.td>
                            @if (!$hasData)
                                <x-ui.badge color="zinc">Nema podataka</x-ui.badge>
                            @elseif ($isPositive)
                                <x-ui.badge color="green">Pozitivan</x-ui.badge>
                            @else
                                <x-ui.badge color="red">Negativan</x-ui.badge>
                            @endif
                        </x-ui.table.td>
                    </x-ui.table.row>
                @endforeach
            </x-ui.table.body>
        </x-ui.table>
    </div>

    {{-- Recommendations --}}
    <div class="rounded-xl border border-zinc-200 bg-white p-5 dark:border-zinc-700 dark:bg-zinc-900">
        <h3 class="mb-4 font-semibold text-zinc-900 dark:text-zinc-100">Šta bi trebalo popraviti</h3>
        <div class="space-y-3 text-sm text-zinc-600 dark:text-zinc-400">
            @php
                $positiveCount = count(array_filter($months, fn($m) => $m['net'] > 0));
                $negativeMonths = array_filter($months, fn($m) => ($m['income'] > 0 || $m['expense'] > 0) && $m['net'] <= 0);
                $emptyMonths = count(array_filter($months, fn($m) => $m['income'] === 0.0 && $m['expense'] === 0.0));
            @endphp

            @if ($positiveCount < 8)
                <div class="flex items-start gap-2">
                    <span class="mt-0.5 text-amber-500">●</span>
                    <p>Povećajte broj pozitivnih meseci — trenutno {{ $positiveCount }}/12. Cilj je najmanje 8 pozitivnih meseci za SAFE signal.</p>
                </div>
            @endif

            @if ($totals['expense'] > $totals['income'])
                <div class="flex items-start gap-2">
                    <span class="mt-0.5 text-red-500">●</span>
                    <p>Rashodi premašuju prihode za {{ $fmt($totals['expense'] - $totals['income']) }}. Smanjite troškove ili povećajte prihode.</p>
                </div>
            @endif

            @if ($emptyMonths > 0)
                <div class="flex items-start gap-2">
                    <span class="mt-0.5 text-zinc-400">●</span>
                    <p>{{ $emptyMonths }} {{ $emptyMonths === 1 ? 'mesec nema' : 'meseca nemaju' }} evidentiranih transakcija. Proverite da li su svi prihodi/rashodi uneseni.</p>
                </div>
            @endif

            @if (count($negativeMonths) > 0)
                <div class="flex items-start gap-2">
                    <span class="mt-0.5 text-orange-500">●</span>
                    <p>Negativni meseci: {{ implode(', ', array_column(array_values($negativeMonths), 'label')) }}. Analizirajte rashode u tim mesecima.</p>
                </div>
            @endif

            @if ($signal['status'] === 'safe')
                <div class="flex items-start gap-2">
                    <span class="mt-0.5 text-emerald-500">●</span>
                    <p>Finansijska situacija je stabilna. Preporučena max. investicija ne prelazi 25% prosečnog pozitivnog mesečnog neta.</p>
                </div>
            @endif
        </div>
    </div>

</div>
