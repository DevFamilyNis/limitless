<div class="flex h-full w-full flex-1 flex-col gap-6">
    <div class="grid gap-4 md:grid-cols-3">
        <div class="rounded-xl border border-zinc-200 p-5 dark:border-zinc-700">
            <flux:heading size="lg">Prihod ove godine</flux:heading>
            <div class="mt-3 text-2xl font-semibold">{{ number_format($incomeYear, 2, ',', '.') }} RSD</div>
            <div class="mt-3 text-sm text-zinc-600 dark:text-zinc-300">
                {{ number_format($firstThresholdPercent, 1, ',', '.') }}% od 6M,
                {{ number_format($secondThresholdPercent, 1, ',', '.') }}% od 8M
            </div>
            <div class="mt-2 text-xs text-zinc-500">
                Pragovi: {{ number_format($firstThreshold, 2, ',', '.') }} / {{ number_format($secondThreshold, 2, ',', '.') }} RSD
            </div>
        </div>

        <div class="rounded-xl border border-zinc-200 p-5 dark:border-zinc-700">
            <flux:heading size="lg">Neto ovaj mesec</flux:heading>
            <div class="mt-4 space-y-2 text-sm">
                <div class="flex items-center justify-between">
                    <span>Prihodi</span>
                    <span>{{ number_format($incomeThisMonth, 2, ',', '.') }} RSD</span>
                </div>
                <div class="flex items-center justify-between">
                    <span>Rashodi</span>
                    <span>{{ number_format($expenseThisMonth, 2, ',', '.') }} RSD</span>
                </div>
                <div class="mt-2 flex items-center justify-between border-t border-zinc-200 pt-2 font-semibold dark:border-zinc-700">
                    <span>Neto</span>
                    <span>{{ number_format($netThisMonth, 2, ',', '.') }} RSD</span>
                </div>
            </div>
        </div>

        <div class="rounded-xl border border-zinc-200 p-5 dark:border-zinc-700">
            <flux:heading size="lg">Otvorene fakture</flux:heading>
            <div class="mt-4 space-y-2 text-sm">
                <div class="flex items-center justify-between">
                    <span>Open total</span>
                    <span class="font-medium">{{ $openInvoicesCount }}</span>
                </div>
                <div class="flex items-center justify-between">
                    <span>Open amount</span>
                    <span class="font-medium">{{ number_format($openInvoicesAmount, 2, ',', '.') }} RSD</span>
                </div>
                <div class="flex items-center justify-between">
                    <span>Overdue</span>
                    <span class="flex items-center gap-2">
                        <span class="font-medium">{{ $overdueInvoicesCount }}</span>
                        @if ($overdueInvoicesCount > 0)
                            <flux:badge color="red">Pažnja</flux:badge>
                        @endif
                    </span>
                </div>
            </div>
        </div>
    </div>

    <div class="grid gap-4 lg:grid-cols-2">
        <div class="rounded-xl border border-zinc-200 p-5 dark:border-zinc-700">
            <div class="mb-4 flex items-center justify-between">
                <flux:heading size="lg">Issue fokus</flux:heading>
                @if ($hasIssueBoardRoute)
                    <flux:button variant="ghost" :href="route('issues.board')" wire:navigate>Otvori Issue board</flux:button>
                @else
                    <flux:button variant="ghost" disabled>Issue board ruta nedostaje</flux:button>
                @endif
            </div>

            <div class="grid gap-3 sm:grid-cols-2">
                <div class="rounded-lg border border-zinc-200 p-3 dark:border-zinc-700">
                    <div class="text-xs text-zinc-500">Todo</div>
                    <div class="text-xl font-semibold">{{ $issueTodoCount }}</div>
                </div>
                <div class="rounded-lg border border-zinc-200 p-3 dark:border-zinc-700">
                    <div class="text-xs text-zinc-500">Doing</div>
                    <div class="text-xl font-semibold">{{ $issueDoingCount }}</div>
                </div>
                <div class="rounded-lg border border-zinc-200 p-3 dark:border-zinc-700">
                    <div class="text-xs text-zinc-500">High priority open</div>
                    <div class="text-xl font-semibold">{{ $issueHighOpenCount }}</div>
                </div>
                <div class="rounded-lg border border-zinc-200 p-3 dark:border-zinc-700">
                    <div class="text-xs text-zinc-500">Overdue reminders</div>
                    <div class="text-xl font-semibold">{{ $issueOverdueRemindersCount }}</div>
                </div>
            </div>
        </div>

        <div class="rounded-xl border border-zinc-200 p-5 dark:border-zinc-700">
            <flux:heading size="lg">Sledeći rokovi (7 dana)</flux:heading>

            <div class="mt-4 space-y-2">
                @forelse ($deadlines as $item)
                    <div class="flex items-center justify-between gap-3 rounded-lg border border-zinc-200 p-3 text-sm dark:border-zinc-700">
                        <div class="min-w-0">
                            <div class="flex items-center gap-2">
                                <span class="font-medium">{{ \Illuminate\Support\Carbon::parse($item['date'])->format('d.m.Y') }}</span>
                                <flux:badge>{{ $item['type'] }}</flux:badge>
                                @if (! empty($item['client']))
                                    <flux:badge>{{ $item['client'] }}</flux:badge>
                                @endif
                            </div>
                            <div class="mt-1 truncate">{{ $item['title'] }}</div>
                        </div>

                        @if (! empty($item['url']))
                            <flux:button variant="ghost" :href="$item['url']" wire:navigate>Otvori</flux:button>
                        @else
                            <span class="text-xs text-zinc-500">Nema rute</span>
                        @endif
                    </div>
                @empty
                    <div class="rounded-lg border border-dashed border-zinc-300 p-4 text-sm text-zinc-500 dark:border-zinc-700">
                        Nema rokova u narednih 7 dana.
                    </div>
                @endforelse
            </div>
        </div>
    </div>
</div>
