<?php

declare(strict_types=1);

namespace App\Domain\Contract\Actions;

use App\Domain\Contract\DTO\ChangeContractStatusData;
use App\Domain\Contract\Enums\ContractStatus;
use App\Domain\Contract\Enums\ContractType;
use App\Domain\Contract\Exceptions\MultipleActiveContractException;
use App\Models\Contract;
use Illuminate\Support\Facades\DB;

final class ChangeContractStatusAction
{
    public function execute(ChangeContractStatusData $dto): Contract
    {
        $contract = Contract::query()
            ->where('user_id', $dto->userId)
            ->findOrFail($dto->contractId);

        DB::transaction(function () use ($contract, $dto): void {
            if ($contract->type === ContractType::Ugovor && $dto->status === ContractStatus::Aktivan) {
                $exists = Contract::query()
                    ->where('user_id', $contract->user_id)
                    ->where('client_id', $contract->client_id)
                    ->where('type', ContractType::Ugovor->value)
                    ->where('status', ContractStatus::Aktivan->value)
                    ->where('id', '!=', $contract->id)
                    ->lockForUpdate()
                    ->exists();

                if ($exists) {
                    throw MultipleActiveContractException::forClient();
                }
            }

            $contract->status = $dto->status;
            $contract->save();

            if ($contract->type === ContractType::Ugovor && $dto->status === ContractStatus::Neaktivan) {
                Contract::query()
                    ->where('parent_id', $contract->id)
                    ->update(['status' => ContractStatus::Neaktivan->value]);
            }
        });

        return $contract->refresh();
    }
}
