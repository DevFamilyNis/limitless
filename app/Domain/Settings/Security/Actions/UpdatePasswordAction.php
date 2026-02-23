<?php

declare(strict_types=1);

namespace App\Domain\Settings\Security\Actions;

use App\Domain\Settings\Security\DTO\UpdatePasswordData;
use App\Models\User;

final class UpdatePasswordAction
{
    public function execute(UpdatePasswordData $dto): User
    {
        $user = User::query()->findOrFail($dto->userId);

        $user->update([
            'password' => $dto->password,
        ]);

        return $user;
    }
}
