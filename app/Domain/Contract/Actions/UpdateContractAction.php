<?php

declare(strict_types=1);

namespace App\Domain\Contract\Actions;

use App\Domain\Contract\DTO\UpdateContractData;
use App\Models\Contract;

final class UpdateContractAction
{
    public function execute(UpdateContractData $dto): Contract
    {
        $contract = Contract::query()
            ->where('user_id', $dto->userId)
            ->findOrFail($dto->contractId);

        $contract->fill([
            'start_date' => $dto->startDate,
            'end_date' => $dto->endDate,
            'note' => $dto->note,
        ]);

        $contract->save();

        if ($dto->pdfFile !== null) {
            $contract->addMedia($dto->pdfFile->getRealPath())
                ->usingFileName($dto->pdfFile->getClientOriginalName())
                ->toMediaCollection('pdf');
        }

        return $contract->refresh();
    }
}
