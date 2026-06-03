<?php

declare(strict_types=1);

namespace App\Livewire\Contracts;

use App\Domain\Contract\Actions\CreateContractAction;
use App\Domain\Contract\Actions\UpdateContractAction;
use App\Domain\Contract\DTO\CreateContractData;
use App\Domain\Contract\DTO\UpdateContractData;
use App\Domain\Contract\Enums\ContractType;
use App\Domain\Contract\Exceptions\MultipleActiveContractException;
use App\Models\Client;
use App\Models\Contract;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Livewire\WithFileUploads;

class Form extends Component
{
    use WithFileUploads;

    public ?int $contractId = null;

    public string $clientId = '';

    public string $type = ContractType::Ugovor->value;

    public string $parentId = '';

    public string $startDate = '';

    public string $endDate = '';

    public string $note = '';

    /** @var \Livewire\Features\SupportFileUploads\TemporaryUploadedFile|null */
    public $pdfFile = null;

    public bool $hasPdf = false;

    public function mount(?Contract $contract = null): void
    {
        if ($contract?->exists) {
            $this->contractId = $contract->id;
            $this->clientId = (string) $contract->client_id;
            $this->type = $contract->type->value;
            $this->parentId = $contract->parent_id !== null ? (string) $contract->parent_id : '';
            $this->startDate = $contract->start_date->format('Y-m-d');
            $this->endDate = $contract->end_date?->format('Y-m-d') ?? '';
            $this->note = (string) ($contract->note ?? '');
            $this->hasPdf = $contract->getFirstMedia('pdf') !== null;
        }
    }

    /**
     * @return array<string, array<int, string>>
     */
    protected function rules(): array
    {
        return [
            'clientId' => ['required', 'integer', 'exists:clients,id'],
            'type' => ['required', 'string', 'in:Ugovor,Aneks'],
            'parentId' => ['nullable', 'integer', 'exists:contracts,id', 'required_if:type,Aneks'],
            'startDate' => ['required', 'date'],
            'endDate' => ['nullable', 'date', 'after_or_equal:startDate'],
            'note' => ['nullable', 'string', 'max:2000'],
            'pdfFile' => ['nullable', 'file', 'mimes:pdf', 'max:10240'],
        ];
    }

    public function updatedType(): void
    {
        if ($this->type === ContractType::Ugovor->value) {
            $this->parentId = '';
        }
    }

    public function updatedClientId(): void
    {
        $this->parentId = '';
    }

    public function save(): void
    {
        $this->validate();

        try {
            if ($this->contractId === null) {
                app(CreateContractAction::class)->execute(
                    CreateContractData::fromArray([
                        'user_id' => Auth::id(),
                        'client_id' => $this->clientId,
                        'parent_id' => $this->parentId !== '' ? $this->parentId : null,
                        'type' => $this->type,
                        'start_date' => $this->startDate,
                        'end_date' => $this->endDate !== '' ? $this->endDate : null,
                        'note' => $this->note,
                        'pdf_file' => $this->pdfFile,
                    ])
                );

                session()->flash('status', __('messages.contracts.flash_created'));
            } else {
                app(UpdateContractAction::class)->execute(
                    UpdateContractData::fromArray([
                        'contract_id' => $this->contractId,
                        'user_id' => Auth::id(),
                        'start_date' => $this->startDate,
                        'end_date' => $this->endDate !== '' ? $this->endDate : null,
                        'note' => $this->note,
                        'pdf_file' => $this->pdfFile,
                    ])
                );

                session()->flash('status', __('messages.contracts.flash_updated'));
            }

            $this->redirectRoute('contracts.index');
        } catch (MultipleActiveContractException $e) {
            $this->addError('clientId', $e->getMessage());
        }
    }

    public function render(): View
    {
        /** @var Collection<int, Contract> $parentContracts */
        $parentContracts = collect();

        if ($this->type === ContractType::Aneks->value && $this->clientId !== '') {
            $parentContracts = Contract::query()
                ->where('user_id', Auth::id())
                ->where('client_id', $this->clientId)
                ->where('type', ContractType::Ugovor->value)
                ->orderByDesc('start_date')
                ->get();
        }

        return view('livewire.contracts.form', [
            'clients' => Client::query()->where('user_id', Auth::id())->where('is_active', true)->orderBy('display_name')->get(),
            'parentContracts' => $parentContracts,
            'isEditing' => $this->contractId !== null,
            'types' => ContractType::cases(),
        ])->layout('layouts.app', [
            'title' => $this->contractId !== null
                ? __('messages.contracts.form_edit_title')
                : __('messages.contracts.form_new_title'),
        ]);
    }
}
