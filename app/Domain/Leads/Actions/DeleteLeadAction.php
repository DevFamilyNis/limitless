<?php

declare(strict_types=1);

namespace App\Domain\Leads\Actions;

use App\Domain\Leads\DTO\DeleteLeadData;
use App\Models\Lead;

final class DeleteLeadAction
{
    public function execute(DeleteLeadData $dto): void
    {
        Lead::query()->findOrFail($dto->leadId)->delete();
    }
}
