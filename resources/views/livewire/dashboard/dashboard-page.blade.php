@php use Illuminate\Support\Carbon; @endphp
<div class="flex h-full w-full flex-1 flex-col gap-6">

    <div class="grid gap-4 md:grid-cols-3">
        <div class="rounded-xl border border-zinc-200 bg-radial-[at_25%_25%] from-white to-zinc-100 to-75% p-5 dark:border-zinc-700 dark:from-zinc-800 dark:to-zinc-900">
            <flux:heading size="lg">@lang('messages.text.incomeThisYear')</flux:heading>
            <div class="mt-3 text-2xl font-semibold">{{ number_format($incomeYear, 2, ',', '.') }} RSD</div>
            <div class="mt-3 text-sm text-zinc-600 dark:text-zinc-300">
                {{ number_format($firstThresholdPercent, 1, ',', '.') }}% od 6M,
                {{ number_format($secondThresholdPercent, 1, ',', '.') }}% od 8M
            </div>
            <div class="mt-2 text-xs text-zinc-500">
                @lang('messages.text.limit'): {{ number_format($firstThreshold, 2, ',', '.') }} / {{ number_format($secondThreshold, 2, ',', '.') }} RSD
            </div>
        </div>

        <div class="rounded-xl border border-zinc-200 bg-radial-[at_25%_25%] from-white to-zinc-100 to-75% p-5 dark:border-zinc-700 dark:from-zinc-800 dark:to-zinc-900">
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

        <div class="rounded-xl border border-zinc-200 bg-radial-[at_25%_25%] from-white to-zinc-100 to-75% p-5 dark:border-zinc-700 dark:from-zinc-800 dark:to-zinc-900">
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
                            <x-ui.badge color="red">{{ $overdueInvoicesCount }}</x-ui.badge>
                        @endif
                    </span>
                </div>
            </div>
        </div>
    </div>

    {{-- Cashflow chart + monthly report --}}
    <div class="grid items-stretch gap-4 lg:grid-cols-2">
        <livewire:dashboard.cashflow-chart />
        @include('livewire.dashboard.monthly-report', ['report' => $monthlyReport])
    </div>

    {{-- Operational overview table --}}
    @include('livewire.dashboard.operational-overview', [
        'items'                      => $operationalItems,
        'issueTodoCount'             => $issueTodoCount,
        'issueDoingCount'            => $issueDoingCount,
        'issueHighOpenCount'         => $issueHighOpenCount,
        'issueOverdueRemindersCount' => $issueOverdueRemindersCount,
    ])

</div>
