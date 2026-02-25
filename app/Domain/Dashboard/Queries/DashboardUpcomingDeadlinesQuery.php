<?php

declare(strict_types=1);

namespace App\Domain\Dashboard\Queries;

use App\Models\Invoice;
use App\Models\Issue;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Route;

final class DashboardUpcomingDeadlinesQuery
{
    /**
     * @return Collection<int, array{date:string,type:string,title:string,client:?string,url:?string}>
     */
    public function execute(int $userId): Collection
    {
        $today = Carbon::today();
        $weekEnd = Carbon::today()->addDays(7);

        $issueDueItems = Issue::query()
            ->join('projects', 'projects.id', '=', 'issues.project_id')
            ->join('issue_statuses', 'issue_statuses.id', '=', 'issues.status_id')
            ->leftJoin('clients', 'clients.id', '=', 'issues.client_id')
            ->where('issue_statuses.key', '!=', 'done')
            ->whereBetween('issues.due_date', [$today->toDateString(), $weekEnd->toDateString()])
            ->orderBy('issues.due_date')
            ->limit(8)
            ->get(['issues.id', 'issues.title', 'issues.due_date', 'clients.display_name as client_name'])
            ->map(function ($issue): array {
                $url = null;

                if (Route::has('issues.show')) {
                    $url = route('issues.show', ['issue' => $issue->id]);
                } elseif (Route::has('issues.edit')) {
                    $url = route('issues.edit', ['issue' => $issue->id]);
                }

                return [
                    'date' => (string) $issue->due_date,
                    'type' => 'Issue',
                    'title' => (string) $issue->title,
                    'client' => $issue->client_name,
                    'url' => $url,
                ];
            });

        $invoiceDueItems = Invoice::query()
            ->join('clients', 'clients.id', '=', 'invoices.client_id')
            ->join('invoice_statuses', 'invoice_statuses.id', '=', 'invoices.status_id')
            ->whereNotIn('invoice_statuses.key', ['paid', 'canceled'])
            ->whereBetween('invoices.due_date', [$today->toDateString(), $weekEnd->toDateString()])
            ->orderBy('invoices.due_date')
            ->limit(8)
            ->get(['invoices.id', 'invoices.invoice_number', 'invoices.due_date', 'clients.display_name as client_name'])
            ->map(function ($invoice): array {
                $url = null;

                if (Route::has('invoices.show')) {
                    $url = route('invoices.show', ['invoice' => $invoice->id]);
                } elseif (Route::has('invoices.edit')) {
                    $url = route('invoices.edit', ['invoice' => $invoice->id]);
                }

                return [
                    'date' => (string) $invoice->due_date,
                    'type' => 'Faktura',
                    'title' => (string) $invoice->invoice_number,
                    'client' => $invoice->client_name,
                    'url' => $url,
                ];
            });

        return collect()
            ->merge($issueDueItems)
            ->merge($invoiceDueItems)
            ->sortBy('date')
            ->take(8)
            ->values();
    }
}
