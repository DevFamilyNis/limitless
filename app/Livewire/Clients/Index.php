<?php

namespace App\Livewire\Clients;

use App\Models\Client;
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

    public function toggleActive(int $clientId): void
    {
        $client = Client::query()
            ->where('user_id', Auth::id())
            ->findOrFail($clientId);

        $client->update([
            'is_active' => ! $client->is_active,
        ]);

        session()->flash('status', 'Status klijenta je uspešno ažuriran.');
    }

    public function deleteClient(int $clientId): void
    {
        $client = Client::query()
            ->where('user_id', Auth::id())
            ->findOrFail($clientId);

        if (! $client->canBeDeleted()) {
            session()->flash('error', 'Klijent ne može biti obrisan jer ima fakture ili transakcije.');

            return;
        }

        $client->delete();

        session()->flash('status', 'Klijent je uspešno obrisan.');
    }

    public function render(): View
    {
        $clients = Client::query()
            ->with(['type', 'company', 'person'])
            ->where('user_id', Auth::id())
            ->when($this->search !== '', function ($query): void {
                $query->where(function ($innerQuery): void {
                    $innerQuery
                        ->where('display_name', 'like', '%'.$this->search.'%')
                        ->orWhere('email', 'like', '%'.$this->search.'%')
                        ->orWhere('phone', 'like', '%'.$this->search.'%')
                        ->orWhereHas('person', function ($personQuery): void {
                            $personQuery
                                ->where('first_name', 'like', '%'.$this->search.'%')
                                ->orWhere('last_name', 'like', '%'.$this->search.'%');
                        });
                });
            })
            ->when($this->statusFilter === 'active', fn ($query) => $query->where('is_active', true))
            ->when($this->statusFilter === 'inactive', fn ($query) => $query->where('is_active', false))
            ->latest('id')
            ->paginate(10);

        return view('livewire.clients.index', [
            'clients' => $clients,
        ])->layout('layouts.app', [
            'title' => 'Klijenti',
        ]);
    }
}
