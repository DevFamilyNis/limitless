<?php

declare(strict_types=1);

namespace App\Domain\Dashboard\Queries;

use App\Enums\InvoiceStatusKey;
use App\Enums\IssuePriorityKey;
use App\Enums\IssueStatusKey;
use App\Models\Invoice;
use App\Models\Issue;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;

final class OperationalOverviewQuery
{
    /**
     * Returns all operational items (active issues + upcoming invoices) for the dashboard table.
     * Items are issues that are: active (todo/doing) OR high priority OR upcoming/overdue OR reminders.
     * Also includes unpaid invoices due within 7 days.
     *
     * @return Collection<int, array{
     *   id: int,
     *   type: string,
     *   title: string,
     *   status_key: string,
     *   status_label: string,
     *   priority_key: string,
     *   priority_label: string,
     *   due_date: string|null,
     *   due_date_raw: string|null,
     *   is_overdue: bool,
     *   is_upcoming: bool,
     *   is_reminder: bool,
     *   is_high_priority: bool,
     *   client: string|null,
     *   assignee: string|null,
     *   amount: float|null,
     *   url: string
     * }>
     */
    public function execute(): Collection
    {
        $today = Carbon::today();
        $weekEnd = $today->copy()->addDays(7);

        $highKeys = [IssuePriorityKey::High->value, IssuePriorityKey::Urgent->value];
        $activeStatuses = [IssueStatusKey::Todo->value, IssueStatusKey::Doing->value];

        // Fetch all non-done issues that need attention
        $issues = Issue::query()
            ->join('issue_statuses', 'issue_statuses.id', '=', 'issues.status_id')
            ->leftJoin('issue_priorities', 'issue_priorities.id', '=', 'issues.priority_id')
            ->leftJoin('issue_categories', 'issue_categories.id', '=', 'issues.category_id')
            ->leftJoin('clients', 'clients.id', '=', 'issues.client_id')
            ->leftJoin('users as assignees', 'assignees.id', '=', 'issues.assignee_id')
            ->where('issue_statuses.key', '!=', IssueStatusKey::Done->value)
            ->where(function ($q) use ($activeStatuses, $highKeys, $today): void {
                $q->whereIn('issue_statuses.key', $activeStatuses)
                    ->orWhereIn('issue_priorities.key', $highKeys)
                    ->orWhere('issues.due_date', '<=', $today->copy()->addDays(7)->toDateString());
            })
            ->orderBy('issues.due_date')
            ->limit(100)
            ->get([
                'issues.id',
                'issues.title',
                'issues.due_date',
                'issue_statuses.key as status_key',
                'issue_statuses.name as status_label',
                'issue_priorities.key as priority_key',
                'issue_priorities.name as priority_label',
                'issue_categories.name as category_name',
                'clients.display_name as client_name',
                'assignees.name as assignee_name',
            ])
            ->map(function ($row) use ($today, $weekEnd, $highKeys): array {
                $dueRaw = $row->due_date;
                $due = $dueRaw ? Carbon::parse($dueRaw) : null;

                return [
                    'id' => $row->id,
                    'type' => 'task',
                    'title' => (string) $row->title,
                    'status_key' => (string) $row->status_key,
                    'status_label' => (string) $row->status_label,
                    'priority_key' => $row->priority_key ?? IssuePriorityKey::Low->value,
                    'priority_label' => $row->priority_label ?? 'Nizak',
                    'due_date' => $due?->format('d.m.Y'),
                    'due_date_raw' => $dueRaw,
                    'is_overdue' => $due !== null && $due->lt($today),
                    'is_upcoming' => $due !== null && ! $due->lt($today) && $due->lte($weekEnd),
                    'is_reminder' => strtolower((string) $row->category_name) === 'reminder',
                    'is_high_priority' => in_array($row->priority_key, $highKeys, true),
                    'client' => $row->client_name,
                    'assignee' => $row->assignee_name,
                    'amount' => null,
                    'url' => route('issues.show', ['issue' => $row->id]),
                ];
            });

        // Fetch unpaid invoices due within 7 days
        $closedKeys = [InvoiceStatusKey::Paid->value, InvoiceStatusKey::Canceled->value];

        $invoices = Invoice::query()
            ->join('clients', 'clients.id', '=', 'invoices.client_id')
            ->join('invoice_statuses', 'invoice_statuses.id', '=', 'invoices.status_id')
            ->whereNotIn('invoice_statuses.key', $closedKeys)
            ->where('invoices.due_date', '<=', $weekEnd->toDateString())
            ->orderBy('invoices.due_date')
            ->limit(20)
            ->get([
                'invoices.id',
                'invoices.invoice_number',
                'invoices.due_date',
                'invoices.total',
                'clients.display_name as client_name',
            ])
            ->map(function ($row) use ($today, $weekEnd): array {
                $due = Carbon::parse($row->due_date);

                return [
                    'id' => $row->id,
                    'type' => 'invoice',
                    'title' => (string) $row->invoice_number,
                    'status_key' => 'invoice_due',
                    'status_label' => 'Naplata',
                    'priority_key' => $due->lt($today) ? IssuePriorityKey::High->value : IssuePriorityKey::Medium->value,
                    'priority_label' => $due->lt($today) ? 'Kasni' : 'Normalan',
                    'due_date' => $due->format('d.m.Y'),
                    'due_date_raw' => (string) $row->due_date,
                    'is_overdue' => $due->lt($today),
                    'is_upcoming' => ! $due->lt($today) && $due->lte($weekEnd),
                    'is_reminder' => false,
                    'is_high_priority' => $due->lt($today),
                    'client' => $row->client_name,
                    'assignee' => null,
                    'amount' => (float) $row->total,
                    'url' => route('invoices.edit', ['invoice' => $row->id]),
                ];
            });

        // Merge and sort: overdue first, then by due date, then by priority
        return $issues
            ->merge($invoices)
            ->sortBy([
                fn ($a, $b) => (int) $b['is_overdue'] <=> (int) $a['is_overdue'],
                fn ($a, $b) => ($a['due_date_raw'] ?? '9999-12-31') <=> ($b['due_date_raw'] ?? '9999-12-31'),
            ])
            ->values();
    }
}
