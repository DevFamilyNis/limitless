<?php

declare(strict_types=1);

namespace App\Domain\Settings\Security\Actions;

use App\Domain\Settings\Security\DTO\DeleteUserData;
use App\Models\User;

final class DeleteUserAction
{
    public function execute(DeleteUserData $dto): void
    {
        User::query()->findOrFail($dto->userId)->delete();
    }
}
