<?php

namespace App\Livewire\Settings;

use App\Concerns\PasswordValidationRules;
use App\Domain\Settings\Security\Actions\DeleteUserAction;
use App\Domain\Settings\Security\DTO\DeleteUserData;
use App\Livewire\Actions\Logout;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;

class DeleteUserForm extends Component
{
    use PasswordValidationRules;

    public string $password = '';

    /**
     * Delete the currently authenticated user.
     */
    public function deleteUser(Logout $logout): void
    {
        $this->validate([
            'password' => $this->currentPasswordRules(),
        ]);

        $userId = Auth::id();
        $logout();
        app(DeleteUserAction::class)->execute(
            DeleteUserData::fromArray([
                'user_id' => $userId,
            ])
        );

        $this->redirect('/', navigate: true);
    }
}
