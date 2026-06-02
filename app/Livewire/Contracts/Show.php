<?php

declare(strict_types=1);

namespace App\Livewire\Contracts;

use App\Domain\Contract\Actions\ChangeContractStatusAction;
use App\Domain\Contract\DTO\ChangeContractStatusData;
use App\Domain\Contract\Enums\ContractStatus;
use App\Domain\Contract\Enums\ContractType;
use App\Domain\Contract\Exceptions\MultipleActiveContractException;
use App\Models\Contract;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class Show extends Component
{
    public Contract $contract;

    public function mount(Contract $contract): void
    {
        abort_if($contract->user_id !== Auth::id(), 403);

        $this->contract = $contract->load(['client', 'parentContract.client', 'annexes.client']);
    }

    public function changeStatus(string $status): void
    {
        try {
            app(ChangeContractStatusAction::class)->execute(
                ChangeContractStatusData::fromArray([
                    'contract_id' => $this->contract->id,
                    'user_id' => Auth::id(),
                    'status' => $status,
                ])
            );

            $this->contract = $this->contract->fresh(['client', 'parentContract.client', 'annexes.client']);

            session()->flash('status', __('messages.contracts.flash_status_changed'));
        } catch (MultipleActiveContractException $e) {
            session()->flash('error', $e->getMessage());
        }
    }

    public function render(): View
    {
        return view('livewire.contracts.show', [
            'isUgovor' => $this->contract->type === ContractType::Ugovor,
            'isAktivan' => $this->contract->status === ContractStatus::Aktivan,
        ])->layout('layouts.app', [
            'title' => __('messages.contracts.title').': '.$this->contract->client->display_name,
        ]);
    }
}
