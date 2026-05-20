<?php

declare(strict_types=1);

namespace App\Domain\Dashboard\Queries;

use App\Enums\InvoiceStatusKey;
use App\Enums\IssuePriorityKey;
use App\Enums\IssueStatusKey;
use App\Models\Invoice;
use App\Models\Issue;
use App\Models\IssueCategory;
use App\Models\TaxYear;
use App\Models\Transaction;
use Illuminate\Support\Carbon;

final class DashboardMetricsQuery
{
    /**
     * @return array<string, float|int>
     */
    public function execute(int $userId): array
    {
        $today = Carbon::today();
        $year = (int) $today->year;
        $month = (int) $today->month;

        $taxYear = TaxYear::query()
            ->where('year', $year)
            ->first();

        $firstThreshold = (float) ($taxYear?->first_threshold_amount ?? 6000000);
        $secondThreshold = (float) ($taxYear?->second_threshold_amount ?? 8000000);

        $transactionBaseQuery = Transaction::query()
            ->join('categories', 'categories.id', '=', 'transactions.category_id')
            ->join('category_types', 'category_types.id', '=', 'categories.category_type_id');

        $incomeYear = (float) (clone $transactionBaseQuery)
            ->where('category_types.key', 'income')
            ->whereYear('transactions.date', $year)
            ->sum('transactions.amount');

        $incomeThisMonth = (float) (clone $transactionBaseQuery)
            ->where('category_types.key', 'income')
            ->whereYear('transactions.date', $year)
            ->whereMonth('transactions.date', $month)
            ->sum('transactions.amount');

        $expenseThisMonth = (float) (clone $transactionBaseQuery)
            ->where('category_types.key', 'expense')
            ->whereYear('transactions.date', $year)
            ->whereMonth('transactions.date', $month)
            ->sum('transactions.amount');

        $closedInvoiceKeys = [InvoiceStatusKey::Paid->value, InvoiceStatusKey::Canceled->value];
        $highPriorityKeys = [IssuePriorityKey::High->value, IssuePriorityKey::Urgent->value];
        $reminderCategoryId = IssueCategory::query()->where('name', 'Reminder')->value('id');

        $invoiceMetrics = Invoice::query()
            ->join('invoice_statuses', 'invoice_statuses.id', '=', 'invoices.status_id')
            ->join('clients', 'clients.id', '=', 'invoices.client_id')
            ->selectRaw('COALESCE(SUM(CASE WHEN invoice_statuses.`key` NOT IN (?,?) THEN 1 ELSE 0 END), 0) as open_count', $closedInvoiceKeys)
            ->selectRaw('COALESCE(SUM(CASE WHEN invoice_statuses.`key` NOT IN (?,?) THEN invoices.total ELSE 0 END), 0) as open_amount', $closedInvoiceKeys)
            ->selectRaw('COALESCE(SUM(CASE WHEN invoice_statuses.`key` NOT IN (?,?) AND invoices.due_date < ? THEN 1 ELSE 0 END), 0) as overdue_count', [...$closedInvoiceKeys, $today->toDateString()])
            ->first();

        $issueMetrics = Issue::query()
            ->join('projects', 'projects.id', '=', 'issues.project_id')
            ->join('issue_statuses', 'issue_statuses.id', '=', 'issues.status_id')
            ->leftJoin('issue_priorities', 'issue_priorities.id', '=', 'issues.priority_id')
            ->leftJoin('issue_categories', 'issue_categories.id', '=', 'issues.category_id')
            ->selectRaw('COALESCE(SUM(CASE WHEN issue_statuses.`key` = ? THEN 1 ELSE 0 END), 0) as todo_count', [IssueStatusKey::Todo->value])
            ->selectRaw('COALESCE(SUM(CASE WHEN issue_statuses.`key` = ? THEN 1 ELSE 0 END), 0) as doing_count', [IssueStatusKey::Doing->value])
            ->selectRaw('COALESCE(SUM(CASE WHEN issue_statuses.`key` != ? AND issue_priorities.`key` IN (?,?) THEN 1 ELSE 0 END), 0) as high_open_count', [IssueStatusKey::Done->value, ...$highPriorityKeys])
            ->selectRaw('COALESCE(SUM(CASE WHEN issue_statuses.`key` != ? AND issue_categories.id = ? AND issues.due_date < ? THEN 1 ELSE 0 END), 0) as overdue_reminders_count', [IssueStatusKey::Done->value, $reminderCategoryId, $today->toDateString()])
            ->first();

        return [
            'incomeYear' => $incomeYear,
            'firstThreshold' => $firstThreshold,
            'secondThreshold' => $secondThreshold,
            'firstThresholdPercent' => $firstThreshold > 0 ? ($incomeYear / $firstThreshold) * 100 : 0,
            'secondThresholdPercent' => $secondThreshold > 0 ? ($incomeYear / $secondThreshold) * 100 : 0,
            'incomeThisMonth' => $incomeThisMonth,
            'expenseThisMonth' => $expenseThisMonth,
            'openInvoicesCount' => (int) ($invoiceMetrics?->open_count ?? 0),
            'openInvoicesAmount' => (float) ($invoiceMetrics?->open_amount ?? 0),
            'overdueInvoicesCount' => (int) ($invoiceMetrics?->overdue_count ?? 0),
            'issueTodoCount' => (int) ($issueMetrics?->todo_count ?? 0),
            'issueDoingCount' => (int) ($issueMetrics?->doing_count ?? 0),
            'issueHighOpenCount' => (int) ($issueMetrics?->high_open_count ?? 0),
            'issueOverdueRemindersCount' => (int) ($issueMetrics?->overdue_reminders_count ?? 0),
        ];
    }
}
