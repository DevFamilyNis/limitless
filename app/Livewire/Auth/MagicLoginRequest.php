<?php

namespace App\Livewire\Auth;

use App\Actions\Auth\SendMagicLoginLink;
use App\Models\User;
use Livewire\Component;

class MagicLoginRequest extends Component
{
    public string $email = '';

    public function send(): void
    {
        $this->validate([
            'email' => ['required', 'email:rfc'],
        ]);

        $user = User::query()->where('email', $this->email)->first();

        if ($user) {
            app(SendMagicLoginLink::class)->handle($user);
        }

        session()->flash('status', 'Ako email postoji u sistemu, poslali smo link za prijavu.');
        $this->reset('email');
    }

    public function render()
    {
        return view('livewire.auth.magic-login-request')
            ->layout('layouts::auth.simple');
    }
}
