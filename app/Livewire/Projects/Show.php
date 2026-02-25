<?php

namespace App\Livewire\Projects;

use App\Models\ClientProjectRate;
use App\Models\InvoiceItem;
use App\Models\Project;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Carbon;
use Livewire\Component;

class Show extends Component
{
    public Project $project;

    public float $currentMonthInvoiceTotal = 0.0;

    /** @var array<int, array{month:string,total:float}> */
    public array $monthlyTotals = [];

    public function mount(Project $project): void
    {

        $this->project = $project->load(['user']);

        $this->currentMonthInvoiceTotal = $this->calculateCurrentMonthTotal();
        $this->monthlyTotals = $this->calculateLastSixMonthsTotals();
    }

    public function render(): View
    {
        $clients = ClientProjectRate::query()
            ->with(['client.type', 'client.person'])
            ->where('project_id', $this->project->id)
            ->get()
            ->pluck('client')
            ->filter()
            ->unique('id')
            ->values();

        return view('livewire.projects.show', [
            'clients' => $clients,
        ])->layout('layouts.app', [
            'title' => __('messages.projects.title').': '.$this->project->name,
        ]);
    }

    private function calculateCurrentMonthTotal(): float
    {
        return (float) InvoiceItem::query()
            ->join('invoices', 'invoices.id', '=', 'invoice_items.invoice_id')
            ->join('clients', 'clients.id', '=', 'invoices.client_id')
            ->where('invoice_items.project_id', $this->project->id)
            ->whereYear('invoices.issue_date', (int) now()->year)
            ->whereMonth('invoices.issue_date', (int) now()->month)
            ->sum('invoice_items.amount');
    }

    /**
     * @return array<int, array{month:string,total:float}>
     */
    private function calculateLastSixMonthsTotals(): array
    {
        $result = [];

        for ($i = 5; $i >= 0; $i--) {
            $month = Carbon::now()->startOfMonth()->subMonths($i);
            $total = (float) InvoiceItem::query()
                ->join('invoices', 'invoices.id', '=', 'invoice_items.invoice_id')
                ->join('clients', 'clients.id', '=', 'invoices.client_id')
                ->where('invoice_items.project_id', $this->project->id)
                ->whereYear('invoices.issue_date', (int) $month->year)
                ->whereMonth('invoices.issue_date', (int) $month->month)
                ->sum('invoice_items.amount');

            $result[] = [
                'month' => $month->translatedFormat('m.Y'),
                'total' => $total,
            ];
        }

        return $result;
    }
}
