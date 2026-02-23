<?php

declare(strict_types=1);

namespace App\Domain\Settings\Profile\Actions;

use App\Domain\Settings\Profile\DTO\UpdateProfileData;
use App\Models\User;

final class UpdateProfileAction
{
    public function execute(UpdateProfileData $dto): User
    {
        $user = User::query()->findOrFail($dto->userId);

        $user->fill([
            'name' => $dto->name,
            'email' => $dto->email,
        ]);

        if ($user->isDirty('email')) {
            $user->email_verified_at = null;
        }

        $user->save();

        return $user;
    }
}
