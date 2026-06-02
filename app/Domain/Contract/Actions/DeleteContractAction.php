<?php

declare(strict_types=1);

namespace App\Domain\Contract\Actions;

use App\Domain\Contract\DTO\DeleteContractData;
use App\Models\Contract;

final class DeleteContractAction
{
    public function execute(DeleteContractData $dto): void
    {
        $contract = Contract::query()
            ->where('user_id', $dto->userId)
            ->with('annexes')
            ->findOrFail($dto->contractId);

        // Delete annexes explicitly so Spatie removes their media files
        foreach ($contract->annexes as $annex) {
            $annex->delete();
        }

        $contract->delete();
    }
}
