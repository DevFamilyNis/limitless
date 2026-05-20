<?php

declare(strict_types=1);

namespace App\Livewire\Admin\Roles;

use App\Enums\PermissionKey;
use App\Enums\RoleKey;
use App\Models\User;
use Illuminate\Contracts\View\View;
use Illuminate\Validation\Rule;
use Livewire\Component;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class Index extends Component
{
    public string $newRoleName = '';

    /** @var array<int, string> */
    public array $newRolePermissions = [];

    public function togglePermission(int $roleId, string $permissionName): void
    {
        $this->authorize(PermissionKey::ManageRoles->value);

        $role = Role::findOrFail($roleId);

        if ($role->name === RoleKey::SuperAdmin->value) {
            return;
        }

        if ($role->hasPermissionTo($permissionName)) {
            $role->revokePermissionTo($permissionName);
        } else {
            $role->givePermissionTo($permissionName);
        }

        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();
    }

    public function createRole(): void
    {
        $this->authorize(PermissionKey::ManageRoles->value);

        $validated = $this->validate([
            'newRoleName' => ['required', 'string', 'max:255', Rule::unique('roles', 'name')],
            'newRolePermissions' => ['array'],
            'newRolePermissions.*' => ['string', 'exists:permissions,name'],
        ]);

        $role = Role::create(['name' => $validated['newRoleName'], 'guard_name' => 'web']);
        $role->syncPermissions($this->newRolePermissions);

        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        $roleName = $this->newRoleName;
        $this->newRoleName = '';
        $this->newRolePermissions = [];

        session()->flash('status', "Rola '{$roleName}' je kreirana.");
    }

    public function deleteRole(int $roleId): void
    {
        $this->authorize(PermissionKey::ManageRoles->value);

        $role = Role::findOrFail($roleId);

        $systemRoles = array_column(RoleKey::cases(), 'value');
        if (in_array($role->name, $systemRoles, true)) {
            session()->flash('error', "Sistemska rola '{$role->name}' ne može biti obrisana.");

            return;
        }

        $usersCount = User::role($role->name)->count();
        if ($usersCount > 0) {
            session()->flash('error', "Rola '{$role->name}' je dodeljena {$usersCount} korisnicima i ne može biti obrisana.");

            return;
        }

        $roleName = $role->name;
        $role->delete();

        session()->flash('status', "Rola '{$roleName}' je obrisana.");
    }

    /**
     * @return array<string, array<string, string>>
     */
    public function permissionGroups(): array
    {
        return [
            'Dashboard' => [
                'view-dashboard' => 'Pregled dashboarda',
            ],
            'Klijenti' => [
                'view-clients' => 'Pregled klijenata',
                'manage-clients' => 'Upravljanje klijentima',
            ],
            'Projekti' => [
                'view-projects' => 'Pregled projekata',
                'manage-projects' => 'Upravljanje projektima',
            ],
            'Fakture' => [
                'view-invoices' => 'Pregled faktura',
                'manage-invoices' => 'Kreiranje i izmena faktura',
            ],
            'Transakcije' => [
                'view-transactions' => 'Pregled transakcija',
                'manage-transactions' => 'Upravljanje transakcijama i rashodima',
            ],
            'Leadovi' => [
                'view-leads' => 'Pregled leadova',
                'manage-leads' => 'Kreiranje i izmena leadova',
            ],
            'Issues / Taskovi' => [
                'view-issues' => 'Pregled taskova',
                'manage-issues' => 'Kreiranje, izmena i komentarisanje taskova',
            ],
            'KPO' => [
                'view-kpo' => 'Pregled KPO izveštaja',
                'manage-kpo' => 'Generisanje i zaključavanje KPO',
            ],
            'Administracija' => [
                'manage-categories' => 'Upravljanje kategorijama',
                'manage-tax-years' => 'Upravljanje poreskim godinama',
                'manage-settings' => 'Sistemska konfiguracija (statusi, prioriteti)',
                'manage-users' => 'Upravljanje korisnicima (admin panel)',
                'manage-roles' => 'Upravljanje rolama i permisijama',
            ],
        ];
    }

    public function render(): View
    {
        $roles = Role::query()
            ->with('permissions')
            ->withCount('users')
            ->orderBy('name')
            ->get();

        return view('livewire.admin.roles.index', [
            'roles' => $roles,
            'permissionGroups' => $this->permissionGroups(),
            'superAdminRole' => RoleKey::SuperAdmin->value,
        ])->layout('layouts.app', [
            'title' => 'Role i permisije',
        ]);
    }
}
