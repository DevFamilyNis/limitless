<?php

namespace App\Livewire\Settings;

use App\Concerns\PasswordValidationRules;
use App\Domain\Settings\Security\Actions\UpdatePasswordAction;
use App\Domain\Settings\Security\DTO\UpdatePasswordData;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use Livewire\Component;

class Password extends Component
{
    use PasswordValidationRules;

    public string $current_password = '';

    public string $password = '';

    public string $password_confirmation = '';

    /**
     * Update the password for the currently authenticated user.
     */
    public function updatePassword(): void
    {
        try {
            $validated = $this->validate([
                'current_password' => $this->currentPasswordRules(),
                'password' => $this->passwordRules(),
            ]);
        } catch (ValidationException $e) {
            $this->reset('current_password', 'password', 'password_confirmation');

            throw $e;
        }

        app(UpdatePasswordAction::class)->execute(
            UpdatePasswordData::fromArray([
                'user_id' => Auth::id(),
                'password' => $validated['password'],
            ])
        );

        $this->reset('current_password', 'password', 'password_confirmation');

        $this->dispatch('password-updated');
    }
}
