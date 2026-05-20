<?php

declare(strict_types=1);

namespace App\Livewire\Admin\Users;

use App\Actions\Auth\SendMagicLoginLink;
use App\Enums\PermissionKey;
use App\Enums\RoleKey;
use App\Models\User;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\Auth;
use Livewire\Component;
use Livewire\WithPagination;
use Spatie\Permission\Models\Role;

class Index extends Component
{
    use WithPagination;

    public string $search = '';

    public string $inviteEmail = '';

    public string $inviteName = '';

    public string $inviteRole = '';

    public function mount(): void
    {
        $this->inviteRole = RoleKey::User->value;
    }

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function inviteUser(): void
    {
        $this->authorize(PermissionKey::ManageUsers->value);

        $this->validate([
            'inviteEmail' => ['required', 'email:rfc', 'max:255'],
            'inviteName' => ['required', 'string', 'max:255'],
            'inviteRole' => ['required', 'exists:roles,name'],
        ]);

        $user = User::query()->firstOrCreate(
            ['email' => $this->inviteEmail],
            [
                'name' => $this->inviteName,
                'email_verified_at' => now(),
            ]
        );

        $user->syncRoles([$this->inviteRole]);

        app(SendMagicLoginLink::class)->handle($user);

        $this->inviteEmail = '';
        $this->inviteName = '';
        $this->inviteRole = RoleKey::User->value;

        session()->flash('status', __('messages.admin.flash_user_invited'));
    }

    public function deleteUser(int $userId): void
    {
        $this->authorize(PermissionKey::ManageUsers->value);

        if ($userId === Auth::id()) {
            session()->flash('error', __('messages.admin.flash_cannot_delete_self'));

            return;
        }

        $user = User::query()->findOrFail($userId);
        $superAdminValue = RoleKey::SuperAdmin->value;

        if ($user->hasRole($superAdminValue)) {
            $otherSuperAdminCount = User::query()
                ->whereHas('roles', fn ($q) => $q->where('name', $superAdminValue))
                ->where('id', '!=', $userId)
                ->count();

            if ($otherSuperAdminCount === 0) {
                session()->flash('error', __('messages.admin.flash_last_super_admin_protected'));

                return;
            }
        }

        try {
            $user->delete();
            session()->flash('status', __('messages.admin.flash_user_deleted'));
        } catch (\Throwable) {
            session()->flash('error', __('messages.admin.flash_user_delete_failed'));
        }
    }

    public function assignRole(int $userId, string $roleName): void
    {
        $this->authorize(PermissionKey::ManageUsers->value);

        $user = User::query()->findOrFail($userId);

        $superAdminValue = RoleKey::SuperAdmin->value;

        if ($user->hasRole($superAdminValue) && $roleName !== $superAdminValue) {
            $otherSuperAdminCount = User::query()
                ->whereHas('roles', fn ($q) => $q->where('name', $superAdminValue))
                ->where('id', '!=', $userId)
                ->count();

            if ($otherSuperAdminCount === 0) {
                session()->flash('error', __('messages.admin.flash_last_super_admin_protected'));

                return;
            }
        }

        $user->syncRoles([$roleName]);

        session()->flash('status', __('messages.admin.flash_role_assigned'));
    }

    public function revokeRole(int $userId, string $roleName): void
    {
        $this->authorize(PermissionKey::ManageUsers->value);

        $user = User::query()->findOrFail($userId);

        $superAdminValue = RoleKey::SuperAdmin->value;

        if ($roleName === $superAdminValue) {
            $otherSuperAdminCount = User::query()
                ->whereHas('roles', fn ($q) => $q->where('name', $superAdminValue))
                ->where('id', '!=', $userId)
                ->count();

            if ($otherSuperAdminCount === 0) {
                session()->flash('error', __('messages.admin.flash_last_super_admin_protected'));

                return;
            }
        }

        $user->removeRole($roleName);

        session()->flash('status', __('messages.admin.flash_role_revoked'));
    }

    public function render(): View
    {
        $users = User::query()
            ->with('roles')
            ->when($this->search !== '', function ($query): void {
                $query->where(function ($inner): void {
                    $inner->where('name', 'like', '%'.$this->search.'%')
                        ->orWhere('email', 'like', '%'.$this->search.'%');
                });
            })
            ->orderBy('name')
            ->paginate(15);

        return view('livewire.admin.users.index', [
            'users' => $users,
            'roles' => Role::query()->orderBy('name')->get(),
            'superAdminRole' => RoleKey::SuperAdmin->value,
        ])->layout('layouts.app', [
            'title' => __('messages.admin.users_title'),
        ]);
    }
}
