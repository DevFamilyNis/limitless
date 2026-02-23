<?php

declare(strict_types=1);

namespace App\Livewire\Dashboard;

use App\Domain\Dashboard\Queries\DashboardMetricsQuery;
use App\Domain\Dashboard\Queries\DashboardUpcomingDeadlinesQuery;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use Livewire\Component;

class DashboardPage extends Component
{
    public function render(): View
    {
        $userId = Auth::id();
        $metrics = app(DashboardMetricsQuery::class)->execute($userId);
        $deadlines = app(DashboardUpcomingDeadlinesQuery::class)->execute($userId);
        $netThisMonth = (float) $metrics['incomeThisMonth'] - (float) $metrics['expenseThisMonth'];

        return view('livewire.dashboard.dashboard-page', [
            'incomeYear' => $metrics['incomeYear'],
            'firstThreshold' => $metrics['firstThreshold'],
            'secondThreshold' => $metrics['secondThreshold'],
            'firstThresholdPercent' => $metrics['firstThresholdPercent'],
            'secondThresholdPercent' => $metrics['secondThresholdPercent'],
            'incomeThisMonth' => $metrics['incomeThisMonth'],
            'expenseThisMonth' => $metrics['expenseThisMonth'],
            'netThisMonth' => $netThisMonth,
            'openInvoicesCount' => $metrics['openInvoicesCount'],
            'openInvoicesAmount' => $metrics['openInvoicesAmount'],
            'overdueInvoicesCount' => $metrics['overdueInvoicesCount'],
            'issueTodoCount' => $metrics['issueTodoCount'],
            'issueDoingCount' => $metrics['issueDoingCount'],
            'issueHighOpenCount' => $metrics['issueHighOpenCount'],
            'issueOverdueRemindersCount' => $metrics['issueOverdueRemindersCount'],
            'deadlines' => $deadlines,
            'hasIssueBoardRoute' => Route::has('issues.board'),
        ])->layout('layouts.app', [
            'title' => 'Početna strana',
        ]);
    }
}
