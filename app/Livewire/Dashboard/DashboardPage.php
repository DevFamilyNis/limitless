<?php

declare(strict_types=1);

namespace App\Livewire\Dashboard;

use App\Models\Invoice;
use App\Models\Issue;
use App\Models\TaxYear;
use App\Models\Transaction;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use Livewire\Component;

class DashboardPage extends Component
{
    public function render(): View
    {
        $userId = Auth::id();
        $today = Carbon::today();
        $weekEnd = Carbon::today()->addDays(7);
        $year = (int) $today->year;
        $month = (int) $today->month;

        $taxYear = TaxYear::query()
            ->where('user_id', $userId)
            ->where('year', $year)
            ->first();

        $firstThreshold = (float) ($taxYear?->first_threshold_amount ?? 6000000);
        $secondThreshold = (float) ($taxYear?->second_threshold_amount ?? 8000000);

        $transactionMetrics = Transaction::query()
            ->join('categories', 'categories.id', '=', 'transactions.category_id')
            ->join('category_types', 'category_types.id', '=', 'categories.category_type_id')
            ->where('transactions.user_id', $userId)
            ->selectRaw(
                "COALESCE(SUM(CASE WHEN category_types.`key` = 'income' AND YEAR(transactions.date) = ? THEN transactions.amount ELSE 0 END), 0) as income_year",
                [$year]
            )
            ->selectRaw(
                "COALESCE(SUM(CASE WHEN category_types.`key` = 'income' AND YEAR(transactions.date) = ? AND MONTH(transactions.date) = ? THEN transactions.amount ELSE 0 END), 0) as income_month",
                [$year, $month]
            )
            ->selectRaw(
                "COALESCE(SUM(CASE WHEN category_types.`key` = 'expense' AND YEAR(transactions.date) = ? AND MONTH(transactions.date) = ? THEN transactions.amount ELSE 0 END), 0) as expense_month",
                [$year, $month]
            )
            ->first();

        $incomeYear = (float) ($transactionMetrics?->income_year ?? 0);
        $incomeThisMonth = (float) ($transactionMetrics?->income_month ?? 0);
        $expenseThisMonth = (float) ($transactionMetrics?->expense_month ?? 0);
        $netThisMonth = $incomeThisMonth - $expenseThisMonth;

        $invoiceMetrics = Invoice::query()
            ->join('invoice_statuses', 'invoice_statuses.id', '=', 'invoices.status_id')
            ->join('clients', 'clients.id', '=', 'invoices.client_id')
            ->where('clients.user_id', $userId)
            ->selectRaw("COALESCE(SUM(CASE WHEN invoice_statuses.`key` NOT IN ('paid','canceled') THEN 1 ELSE 0 END), 0) as open_count")
            ->selectRaw("COALESCE(SUM(CASE WHEN invoice_statuses.`key` NOT IN ('paid','canceled') THEN invoices.total ELSE 0 END), 0) as open_amount")
            ->selectRaw(
                "COALESCE(SUM(CASE WHEN invoice_statuses.`key` NOT IN ('paid','canceled') AND invoices.due_date < ? THEN 1 ELSE 0 END), 0) as overdue_count",
                [$today->toDateString()]
            )
            ->first();

        $openInvoicesCount = (int) ($invoiceMetrics?->open_count ?? 0);
        $openInvoicesAmount = (float) ($invoiceMetrics?->open_amount ?? 0);
        $overdueInvoicesCount = (int) ($invoiceMetrics?->overdue_count ?? 0);

        $issueMetrics = Issue::query()
            ->join('projects', 'projects.id', '=', 'issues.project_id')
            ->join('issue_statuses', 'issue_statuses.id', '=', 'issues.status_id')
            ->leftJoin('issue_priorities', 'issue_priorities.id', '=', 'issues.priority_id')
            ->leftJoin('issue_categories', 'issue_categories.id', '=', 'issues.category_id')
            ->where('projects.user_id', $userId)
            ->selectRaw("COALESCE(SUM(CASE WHEN issue_statuses.`key` = 'todo' THEN 1 ELSE 0 END), 0) as todo_count")
            ->selectRaw("COALESCE(SUM(CASE WHEN issue_statuses.`key` = 'doing' THEN 1 ELSE 0 END), 0) as doing_count")
            ->selectRaw("COALESCE(SUM(CASE WHEN issue_statuses.`key` != 'done' AND issue_priorities.`key` IN ('high','urgent') THEN 1 ELSE 0 END), 0) as high_open_count")
            ->selectRaw(
                "COALESCE(SUM(CASE WHEN issue_statuses.`key` != 'done' AND issue_categories.`name` = 'Reminder' AND issues.due_date < ? THEN 1 ELSE 0 END), 0) as overdue_reminders_count",
                [$today->toDateString()]
            )
            ->first();

        $issueDueItems = Issue::query()
            ->join('projects', 'projects.id', '=', 'issues.project_id')
            ->join('issue_statuses', 'issue_statuses.id', '=', 'issues.status_id')
            ->leftJoin('clients', 'clients.id', '=', 'issues.client_id')
            ->where('projects.user_id', $userId)
            ->where('issue_statuses.key', '!=', 'done')
            ->whereBetween('issues.due_date', [$today->toDateString(), $weekEnd->toDateString()])
            ->orderBy('issues.due_date')
            ->limit(8)
            ->get([
                'issues.id',
                'issues.title',
                'issues.due_date',
                'clients.display_name as client_name',
            ])
            ->map(function ($issue): array {
                $url = null;

                if (Route::has('issues.show')) {
                    $url = route('issues.show', ['issue' => $issue->id]);
                } elseif (Route::has('issues.edit')) {
                    $url = route('issues.edit', ['issue' => $issue->id]);
                }

                return [
                    'date' => $issue->due_date,
                    'type' => 'Issue',
                    'title' => $issue->title,
                    'client' => $issue->client_name,
                    'url' => $url,
                ];
            });

        $invoiceDueItems = Invoice::query()
            ->join('clients', 'clients.id', '=', 'invoices.client_id')
            ->join('invoice_statuses', 'invoice_statuses.id', '=', 'invoices.status_id')
            ->where('clients.user_id', $userId)
            ->whereNotIn('invoice_statuses.key', ['paid', 'canceled'])
            ->whereBetween('invoices.due_date', [$today->toDateString(), $weekEnd->toDateString()])
            ->orderBy('invoices.due_date')
            ->limit(8)
            ->get([
                'invoices.id',
                'invoices.invoice_number',
                'invoices.due_date',
                'clients.display_name as client_name',
            ])
            ->map(function ($invoice): array {
                $url = null;

                if (Route::has('invoices.show')) {
                    $url = route('invoices.show', ['invoice' => $invoice->id]);
                } elseif (Route::has('invoices.edit')) {
                    $url = route('invoices.edit', ['invoice' => $invoice->id]);
                }

                return [
                    'date' => $invoice->due_date,
                    'type' => 'Faktura',
                    'title' => $invoice->invoice_number,
                    'client' => $invoice->client_name,
                    'url' => $url,
                ];
            });

        $deadlines = Collection::make()
            ->merge($issueDueItems)
            ->merge($invoiceDueItems)
            ->sortBy('date')
            ->take(8)
            ->values();

        return view('livewire.dashboard.dashboard-page', [
            'incomeYear' => $incomeYear,
            'firstThreshold' => $firstThreshold,
            'secondThreshold' => $secondThreshold,
            'firstThresholdPercent' => $firstThreshold > 0 ? ($incomeYear / $firstThreshold) * 100 : 0,
            'secondThresholdPercent' => $secondThreshold > 0 ? ($incomeYear / $secondThreshold) * 100 : 0,
            'incomeThisMonth' => $incomeThisMonth,
            'expenseThisMonth' => $expenseThisMonth,
            'netThisMonth' => $netThisMonth,
            'openInvoicesCount' => $openInvoicesCount,
            'openInvoicesAmount' => $openInvoicesAmount,
            'overdueInvoicesCount' => $overdueInvoicesCount,
            'issueTodoCount' => (int) ($issueMetrics?->todo_count ?? 0),
            'issueDoingCount' => (int) ($issueMetrics?->doing_count ?? 0),
            'issueHighOpenCount' => (int) ($issueMetrics?->high_open_count ?? 0),
            'issueOverdueRemindersCount' => (int) ($issueMetrics?->overdue_reminders_count ?? 0),
            'deadlines' => $deadlines,
            'hasIssueBoardRoute' => Route::has('issues.board'),
        ])->layout('layouts.app', [
            'title' => 'Početna strana',
        ]);
    }
}
