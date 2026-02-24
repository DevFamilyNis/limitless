<?php

namespace App\Livewire\Clients;

use App\Domain\Clients\Actions\DeleteClientAction;
use App\Domain\Clients\Actions\ToggleClientActiveAction;
use App\Domain\Clients\DTO\DeleteClientData;
use App\Domain\Clients\DTO\ToggleClientActiveData;
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
        app(ToggleClientActiveAction::class)->execute(
            ToggleClientActiveData::fromArray([
                'user_id' => Auth::id(),
                'client_id' => $clientId,
            ])
        );

        session()->flash('status', __('messages.clients.flash_status_updated'));
    }

    public function deleteClient(int $clientId): void
    {
        $deleted = app(DeleteClientAction::class)->execute(
            DeleteClientData::fromArray([
                'user_id' => Auth::id(),
                'client_id' => $clientId,
            ])
        );

        if (! $deleted) {
            session()->flash('error', __('messages.clients.flash_delete_blocked'));

            return;
        }

        session()->flash('status', __('messages.clients.flash_deleted'));
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
            'title' => __('messages.clients.title'),
        ]);
    }
}
