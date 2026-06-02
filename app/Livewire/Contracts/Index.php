<?php

declare(strict_types=1);

namespace App\Livewire\Contracts;

use App\Domain\Contract\Actions\DeleteContractAction;
use App\Domain\Contract\DTO\DeleteContractData;
use App\Domain\Contract\Enums\ContractStatus;
use App\Domain\Contract\Enums\ContractType;
use App\Models\Client;
use App\Models\Contract;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Livewire\WithPagination;

class Index extends Component
{
    use WithPagination;

    public string $clientFilter = '';

    public string $typeFilter = '';

    public string $statusFilter = '';

    public function updatedClientFilter(): void
    {
        $this->resetPage();
    }

    public function updatedTypeFilter(): void
    {
        $this->resetPage();
    }

    public function updatedStatusFilter(): void
    {
        $this->resetPage();
    }

    public function deleteContract(int $contractId): void
    {
        app(DeleteContractAction::class)->execute(
            DeleteContractData::fromArray([
                'user_id' => Auth::id(),
                'contract_id' => $contractId,
            ])
        );

        session()->flash('status', __('messages.contracts.flash_deleted'));
    }

    public function render(): View
    {
        $query = Contract::query()
            ->where('user_id', Auth::id())
            ->with(['client'])
            ->orderByDesc('start_date')
            ->orderByDesc('id');

        if ($this->clientFilter !== '') {
            $query->where('client_id', $this->clientFilter);
        }

        if ($this->typeFilter !== '') {
            $query->where('type', $this->typeFilter);
        }

        if ($this->statusFilter !== '') {
            $query->where('status', $this->statusFilter);
        }

        return view('livewire.contracts.index', [
            'contracts' => $query->paginate(15),
            'clients' => Client::query()->where('user_id', Auth::id())->orderBy('display_name')->get(),
            'types' => ContractType::cases(),
            'statuses' => ContractStatus::cases(),
        ])->layout('layouts.app', [
            'title' => __('messages.contracts.title'),
        ]);
    }
}
