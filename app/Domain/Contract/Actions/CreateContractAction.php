<?php

declare(strict_types=1);

namespace App\Domain\Contract\Actions;

use App\Domain\Contract\DTO\CreateContractData;
use App\Domain\Contract\Enums\ContractStatus;
use App\Domain\Contract\Enums\ContractType;
use App\Domain\Contract\Exceptions\InvalidAnnexParentException;
use App\Domain\Contract\Exceptions\MultipleActiveContractException;
use App\Models\Contract;
use Illuminate\Support\Facades\DB;

final class CreateContractAction
{
    public function execute(CreateContractData $dto): Contract
    {
        if ($dto->type === ContractType::Aneks) {
            $parent = Contract::query()
                ->where('user_id', $dto->userId)
                ->findOrFail($dto->parentId);

            if ($parent->type !== ContractType::Ugovor) {
                throw new InvalidAnnexParentException('Aneks može referencirati samo Ugovor, ne drugi Aneks.');
            }
        }

        $contract = DB::transaction(function () use ($dto): Contract {
            if ($dto->type === ContractType::Ugovor) {
                $exists = Contract::query()
                    ->where('user_id', $dto->userId)
                    ->where('client_id', $dto->clientId)
                    ->where('type', ContractType::Ugovor->value)
                    ->where('status', ContractStatus::Aktivan->value)
                    ->lockForUpdate()
                    ->exists();

                if ($exists) {
                    throw MultipleActiveContractException::forClient();
                }
            }

            return Contract::query()->create([
                'user_id' => $dto->userId,
                'client_id' => $dto->clientId,
                'parent_id' => $dto->parentId,
                'type' => $dto->type->value,
                'status' => ContractStatus::Aktivan->value,
                'start_date' => $dto->startDate,
                'end_date' => $dto->endDate,
                'note' => $dto->note,
            ]);
        });

        if ($dto->pdfFile !== null) {
            $contract->addMedia($dto->pdfFile->getRealPath())
                ->usingFileName($dto->pdfFile->getClientOriginalName())
                ->toMediaCollection('pdf');
        }

        return $contract;
    }
}
