<?php

namespace App\Livewire\ClientProjectRates;

use App\Models\ClientProjectRate;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Livewire\WithPagination;

class Index extends Component
{
    use WithPagination;

    public string $search = '';

    public string $statusFilter = 'all';

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function updatedStatusFilter(): void
    {
        $this->resetPage();
    }

    public function toggleActive(int $rateId): void
    {
        $rate = ClientProjectRate::query()
            ->whereHas('client', fn ($query) => $query->where('user_id', Auth::id()))
            ->findOrFail($rateId);

        $rate->update([
            'is_active' => ! $rate->is_active,
        ]);

        session()->flash('status', 'Status cene je uspešno ažuriran.');
    }

    public function deleteRate(int $rateId): void
    {
        $rate = ClientProjectRate::query()
            ->whereHas('client', fn ($query) => $query->where('user_id', Auth::id()))
            ->findOrFail($rateId);

        $rate->delete();

        session()->flash('status', 'Cena klijenta je uspešno obrisana.');
    }

    public function render(): View
    {
        $rates = ClientProjectRate::query()
            ->with(['client.type', 'client.person', 'project', 'billingPeriod'])
            ->whereHas('client', fn ($query) => $query->where('user_id', Auth::id()))
            ->when($this->search !== '', function ($query): void {
                $query->where(function ($innerQuery): void {
                    $innerQuery
                        ->where('currency', 'like', '%'.$this->search.'%')
                        ->orWhereHas('project', function ($projectQuery): void {
                            $projectQuery
                                ->where('code', 'like', '%'.$this->search.'%')
                                ->orWhere('name', 'like', '%'.$this->search.'%');
                        })
                        ->orWhereHas('client', function ($clientQuery): void {
                            $clientQuery
                                ->where('display_name', 'like', '%'.$this->search.'%')
                                ->orWhereHas('person', function ($personQuery): void {
                                    $personQuery
                                        ->where('first_name', 'like', '%'.$this->search.'%')
                                        ->orWhere('last_name', 'like', '%'.$this->search.'%');
                                });
                        })
                        ->orWhereHas('billingPeriod', fn ($periodQuery) => $periodQuery->where('name', 'like', '%'.$this->search.'%'));
                });
            })
            ->when($this->statusFilter === 'active', fn ($query) => $query->where('is_active', true))
            ->when($this->statusFilter === 'inactive', fn ($query) => $query->where('is_active', false))
            ->latest('id')
            ->paginate(10);

        return view('livewire.client-project-rates.index', [
            'rates' => $rates,
        ])->layout('layouts.app', [
            'title' => 'Cene klijenata',
        ]);
    }
}
