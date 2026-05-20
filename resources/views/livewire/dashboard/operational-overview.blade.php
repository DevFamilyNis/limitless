@php
    $upcomingCount = $items->where('is_upcoming', true)->count() + $items->where('is_overdue', true)->count();
    $remindersCount = $items->where('is_reminder', true)->count();

    $statusBadge = fn(array $item): array => match(true) {
        $item['is_overdue'] => ['label' => 'Kasni', 'color' => 'red'],
        $item['is_upcoming'] => ['label' => 'Rok uskoro', 'color' => 'amber'],
        $item['status_key'] === 'doing' => ['label' => 'U obradi', 'color' => 'blue'],
        $item['status_key'] === 'todo' => ['label' => 'Za obradu', 'color' => 'zinc'],
        $item['type'] === 'invoice' => ['label' => 'Naplata', 'color' => 'amber'],
        default => ['label' => ucfirst($item['status_label']), 'color' => 'zinc'],
    };

    $priorityBadge = fn(array $item): array => match($item['priority_key']) {
        'urgent' => ['label' => 'Urgentno', 'color' => 'red'],
        'high'   => ['label' => 'Visok', 'color' => 'amber'],
        'medium' => ['label' => 'Normalan', 'color' => 'zinc'],
        default  => ['label' => 'Nizak', 'color' => 'zinc'],
    };

    $typeBadge = fn(string $type): array => match($type) {
        'invoice'  => ['label' => 'Faktura', 'color' => 'blue'],
        'reminder' => ['label' => 'Podsetnik', 'color' => 'amber'],
        default    => ['label' => 'Zadatak', 'color' => 'zinc'],
    };

    // Per-row filter tags for Alpine (PHP booleans rendered as JS literals)
    $filterTag = fn(array $item): string => implode(',', array_filter([
        'all',
        $item['status_key'] === 'todo' ? 'pending' : null,
        $item['status_key'] === 'doing' ? 'in_progress' : null,
        $item['is_high_priority'] ? 'high_priority' : null,
        ($item['is_reminder'] || $item['type'] === 'invoice') ? 'reminders' : null,
        ($item['is_upcoming'] || $item['is_overdue']) ? 'upcoming' : null,
    ]));

    $badgeColors = [
        'red'   => 'bg-red-100 text-red-700 dark:bg-red-900/30 dark:text-red-300',
        'amber' => 'bg-amber-100 text-amber-700 dark:bg-amber-900/30 dark:text-amber-300',
        'blue'  => 'bg-blue-100 text-blue-700 dark:bg-blue-900/30 dark:text-blue-300',
        'green' => 'bg-emerald-100 text-emerald-700 dark:bg-emerald-900/30 dark:text-emerald-300',
        'zinc'  => 'bg-zinc-100 text-zinc-600 dark:bg-zinc-800 dark:text-zinc-400',
    ];
@endphp

<div
    class="rounded-xl border border-zinc-200 bg-white dark:border-zinc-700 dark:bg-zinc-900"
    x-data="{ filter: 'all' }"
>
    {{-- Header --}}
    <div class="flex flex-col gap-4 border-b border-zinc-100 px-5 py-4 sm:flex-row sm:items-center sm:justify-between dark:border-zinc-800">
        <div>
            <h2 class="font-semibold text-zinc-900 dark:text-zinc-100">Operativni pregled</h2>
            <p class="mt-0.5 text-sm text-zinc-500 dark:text-zinc-400">Zadaci, rokovi i podsetnici koji zahtevaju pažnju</p>
        </div>
        <flux:button variant="primary" :href="route('issues.index')" wire:navigate size="sm">
            Pregled zadataka
        </flux:button>
    </div>

    {{-- Summary pills + filter tabs --}}
    <div class="flex flex-wrap items-center gap-2 border-b border-zinc-100 px-5 py-3 dark:border-zinc-800">
        @php
            $tabs = [
                ['key' => 'all',           'label' => 'Sve',            'count' => $items->count()],
                ['key' => 'pending',       'label' => 'Za obradu',      'count' => $issueTodoCount],
                ['key' => 'in_progress',   'label' => 'U obradi',       'count' => $issueDoingCount],
                ['key' => 'high_priority', 'label' => 'Visok prioritet','count' => $issueHighOpenCount],
                ['key' => 'reminders',     'label' => 'Rokovi/Fakture', 'count' => $upcomingCount],
            ];
        @endphp

        @foreach ($tabs as $tab)
            <button
                type="button"
                @click="filter = '{{ $tab['key'] }}'"
                :class="filter === '{{ $tab['key'] }}'
                    ? 'bg-zinc-900 text-white dark:bg-zinc-100 dark:text-zinc-900'
                    : 'bg-zinc-100 text-zinc-600 hover:bg-zinc-200 dark:bg-zinc-800 dark:text-zinc-400 dark:hover:bg-zinc-700'"
                class="inline-flex items-center gap-1.5 rounded-md px-3 py-1 text-sm font-medium transition"
            >
                {{ $tab['label'] }}
                @if ($tab['count'] > 0)
                    <span
                        :class="filter === '{{ $tab['key'] }}'
                            ? 'bg-white/20 dark:bg-zinc-900/20'
                            : 'bg-zinc-200 dark:bg-zinc-700'"
                        class="rounded px-1.5 py-0.5 text-xs font-semibold"
                    >{{ $tab['count'] }}</span>
                @endif
            </button>
        @endforeach
    </div>

    {{-- Table --}}
    <div class="overflow-x-auto">
        <table class="w-full min-w-[860px] text-sm">
            <thead>
                <tr class="border-b border-zinc-100 bg-zinc-50 dark:border-zinc-800 dark:bg-white/[0.03]">
                    <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-zinc-500 dark:text-zinc-400">Tip</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-zinc-500 dark:text-zinc-400">Naziv</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-zinc-500 dark:text-zinc-400">Status</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-zinc-500 dark:text-zinc-400">Prioritet</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-zinc-500 dark:text-zinc-400">Rok</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-zinc-500 dark:text-zinc-400">Klijent</th>
                    <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wider text-zinc-500 dark:text-zinc-400">Iznos</th>
                    <th class="px-4 py-3 text-right text-xs font-semibold uppercase tracking-wider text-zinc-500 dark:text-zinc-400">Akcija</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-zinc-100 dark:divide-zinc-800">
                @forelse ($items as $item)
                    @php
                        $tags = $filterTag($item);
                        $sb = $statusBadge($item);
                        $pb = $priorityBadge($item);
                        $tb = $typeBadge($item['is_reminder'] ? 'reminder' : $item['type']);
                    @endphp
                    <tr
                        x-show="filter === 'all' || '{{ $tags }}'.split(',').includes(filter)"
                        class="transition hover:bg-zinc-50 dark:hover:bg-white/[0.02]"
                    >
                        {{-- Tip --}}
                        <td class="whitespace-nowrap px-4 py-3">
                            <span class="inline-flex items-center rounded px-2 py-0.5 text-xs font-medium {{ $badgeColors[$tb['color']] }}">
                                {{ $tb['label'] }}
                            </span>
                        </td>

                        {{-- Naziv --}}
                        <td class="px-4 py-3">
                            <span class="font-medium text-zinc-900 dark:text-zinc-100">{{ $item['title'] }}</span>
                        </td>

                        {{-- Status --}}
                        <td class="whitespace-nowrap px-4 py-3">
                            <span class="inline-flex items-center rounded px-2 py-0.5 text-xs font-medium {{ $badgeColors[$sb['color']] }}">
                                @if ($item['is_overdue'])
                                    <span class="mr-1 size-1.5 rounded-full bg-red-500"></span>
                                @endif
                                {{ $sb['label'] }}
                            </span>
                        </td>

                        {{-- Prioritet --}}
                        <td class="whitespace-nowrap px-4 py-3">
                            @if ($item['type'] !== 'invoice')
                                <span class="inline-flex items-center rounded px-2 py-0.5 text-xs font-medium {{ $badgeColors[$pb['color']] }}">
                                    {{ $pb['label'] }}
                                </span>
                            @else
                                <span class="text-zinc-400 dark:text-zinc-600">—</span>
                            @endif
                        </td>

                        {{-- Rok --}}
                        <td class="whitespace-nowrap px-4 py-3">
                            @if ($item['due_date'])
                                <span class="{{ $item['is_overdue'] ? 'font-semibold text-red-600 dark:text-red-400' : ($item['is_upcoming'] ? 'font-medium text-amber-600 dark:text-amber-400' : 'text-zinc-600 dark:text-zinc-400') }}">
                                    {{ $item['due_date'] }}
                                </span>
                            @else
                                <span class="text-zinc-400 dark:text-zinc-600">—</span>
                            @endif
                        </td>

                        {{-- Klijent --}}
                        <td class="whitespace-nowrap px-4 py-3">
                            @if ($item['client'])
                                <span class="text-zinc-600 dark:text-zinc-400">{{ $item['client'] }}</span>
                            @else
                                <span class="text-zinc-400 dark:text-zinc-600">—</span>
                            @endif
                        </td>

                        {{-- Iznos --}}
                        <td class="whitespace-nowrap px-4 py-3">
                            @if ($item['amount'] !== null)
                                <span class="font-medium text-zinc-700 dark:text-zinc-300">
                                    {{ number_format($item['amount'], 0, ',', '.') }} RSD
                                </span>
                            @else
                                <span class="text-zinc-400 dark:text-zinc-600">—</span>
                            @endif
                        </td>

                        {{-- Akcija --}}
                        <td class="whitespace-nowrap px-4 py-3 text-right">
                            <a
                                href="{{ $item['url'] }}"
                                wire:navigate
                                class="inline-flex items-center rounded-md bg-zinc-900 px-2.5 py-1 text-xs font-medium text-white transition hover:bg-zinc-700 dark:bg-zinc-700 dark:hover:bg-zinc-600"
                            >
                                Otvori
                            </a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8" class="px-4 py-12 text-center text-sm text-zinc-400 dark:text-zinc-600">
                            Nema aktivnih zadataka ili podsetnika koji zahtevaju pažnju.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>

        {{-- Dynamic empty state when filter returns nothing --}}
        <div
            class="hidden px-4 py-12 text-center text-sm text-zinc-400 dark:text-zinc-600"
            x-show="{{ $items->count() > 0 ? 'true' : 'false' }} && document.querySelectorAll('tbody tr[x-show]:not([style*=\"none\"])').length === 0"
        >
            Nema stavki za izabrani filter.
        </div>
    </div>
</div>
