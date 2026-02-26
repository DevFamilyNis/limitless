@php use Illuminate\Support\Carbon; @endphp
<div class="flex h-full w-full flex-1 flex-col gap-6">
    <div class="grid gap-4 md:grid-cols-3">
        <div class="rounded-xl border border-zinc-200 p-5 dark:border-zinc-700">
            <flux:heading size="lg">@lang('messages.text.incomeThisYear')</flux:heading>
            <div class="mt-3 text-2xl font-semibold">{{ number_format($incomeYear, 2, ',', '.') }} RSD</div>
            <div class="mt-3 text-sm text-zinc-600 dark:text-zinc-300">
                {{ number_format($firstThresholdPercent, 1, ',', '.') }}% od 6M,
                {{ number_format($secondThresholdPercent, 1, ',', '.') }}% od 8M
            </div>
            <div class="mt-2 text-xs text-zinc-500">
                @lang('messages.text.limit'): {{ number_format($firstThreshold, 2, ',', '.') }} / {{
                number_format
                ($secondThreshold,
                 2,
                ',', '.') }} RSD
            </div>
        </div>

        <div class="rounded-xl border border-zinc-200 p-5 dark:border-zinc-700">
            <flux:heading size="lg">@lang('messages.text.netoThisMonth')</flux:heading>
            <div class="mt-4 space-y-2 text-sm">
                <div class="flex items-center justify-between">
                    <span>@lang('messages.text.incomes')</span>
                    <span>{{ number_format($incomeThisMonth, 2, ',', '.') }} RSD</span>
                </div>
                <div class="flex items-center justify-between">
                    <span>@lang('messages.text.expenses')</span>
                    <span>{{ number_format($expenseThisMonth, 2, ',', '.') }} RSD</span>
                </div>
                <div class="mt-2 flex items-center justify-between border-t border-zinc-200 pt-2 font-semibold dark:border-zinc-700">
                    <span>@lang('messages.text.neto')</span>
                    <span>{{ number_format($netThisMonth, 2, ',', '.') }} RSD</span>
                </div>
            </div>
        </div>

        <div class="rounded-xl border border-zinc-200 p-5 dark:border-zinc-700">
            <flux:heading size="lg">@lang('messages.text.sentInvoices')</flux:heading>
            <div class="mt-4 space-y-2 text-sm">
                <div class="flex items-center justify-between">
                    <span>@lang('messages.text.totalInvoices')</span>
                    <span class="font-medium">{{ $openInvoicesCount }}</span>
                </div>
                <div class="flex items-center justify-between">
                    <span>@lang('messages.text.totalInvoicesAmount')</span>
                    <span class="font-medium">{{ number_format($openInvoicesAmount, 2, ',', '.') }} RSD</span>
                </div>
                <div class="flex items-center justify-between">
                    <span>@lang('messages.text.unpaidInvoices')</span>
                    <span class="flex items-center gap-2">
                        @if ($overdueInvoicesCount > 0)
                            <flux:badge color="red">{{ $overdueInvoicesCount }}</flux:badge>
                        @endif
                    </span>
                </div>
            </div>
        </div>
    </div>

    <div class="grid items-start gap-4 lg:grid-cols-2">
        <div class="self-start rounded-xl border border-zinc-200 p-5 dark:border-zinc-700">
            <div class="mb-4 flex items-center justify-between">
                <flux:heading size="lg">@lang('messages.text.issues')</flux:heading>
                <flux:button variant="primary" :href="route('issues.index')" wire:navigate>
                    @lang('messages.text.issuesBoard')
                </flux:button>
            </div>

            <div class="grid gap-3 sm:grid-cols-2">
                <div class="rounded-lg border border-zinc-200 p-3 dark:border-zinc-700">
                    <div class="text-xs text-zinc-500">@lang('messages.text.toDo')</div>
                    <div class="text-xl font-semibold">{{ $issueTodoCount }}</div>
                </div>
                <div class="rounded-lg border border-zinc-200 p-3 dark:border-zinc-700">
                    <div class="text-xs text-zinc-500">@lang('messages.text.doing')</div>
                    <div class="text-xl font-semibold">{{ $issueDoingCount }}</div>
                </div>
                <div class="rounded-lg border border-zinc-200 p-3 dark:border-zinc-700">
                    <div class="text-xs text-zinc-500">@lang('messages.text.highPriorityOpen')</div>
                    <div class="text-xl font-semibold">{{ $issueHighOpenCount }}</div>
                </div>
                <div class="rounded-lg border border-zinc-200 p-3 dark:border-zinc-700">
                    <div class="text-xs text-zinc-500">@lang('messages.text.overdueReminders')</div>
                    <div class="text-xl font-semibold">{{ $issueOverdueRemindersCount }}</div>
                </div>
            </div>
        </div>

        <div class="rounded-xl border border-zinc-200 p-5 dark:border-zinc-700">
            <flux:heading size="lg">@lang('messages.text.nextReminder7days')</flux:heading>

            <div class="mt-4 max-h-[30rem] space-y-2 overflow-y-auto pr-1 lg:max-h-[42rem]">
                @forelse ($deadlines as $item)
                    <div class="flex items-center justify-between gap-3 rounded-lg bg-zinc-100 border border-zinc-200
                     p-3
                    text-sm dark:border-zinc-700">
                        <div class="min-w-0">
                            <div class="flex items-center gap-2">
                                <span class="font-medium">{{ Carbon::parse($item['date'])->format('d.m.Y') }}</span>
                                <flux:badge color="lime">{{ $item['type'] }}</flux:badge>
                                @if (! empty($item['client']))
                                    <flux:badge>{{ $item['client'] }}</flux:badge>
                                @endif
                            </div>
                            <div class="mt-1 truncate">{{ $item['title'] }}</div>
                        </div>

                        @if (! empty($item['url']))
                            <flux:button variant="primary" :href="$item['url']" wire:navigate>
                                @lang('messages.text.open')
                            </flux:button>
                        @else
                            <span class="text-xs text-zinc-500">
                                @lang('messages.text.openError')
                            </span>
                        @endif
                    </div>
                @empty
                    <div class="rounded-lg border border-dashed border-zinc-300 p-4 text-sm text-zinc-500 dark:border-zinc-700">
                        @lang('messages.text.emptyReminder7days')
                    </div>
                @endforelse
            </div>
        </div>
    </div>
</div>
