<?php

namespace Database\Seeders;

use App\Enums\PermissionKey;
use App\Enums\RoleKey;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RolesAndPermissionsSeeder extends Seeder
{
    public function run(): void
    {
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        foreach (PermissionKey::cases() as $permission) {
            Permission::query()->firstOrCreate(['name' => $permission->value, 'guard_name' => 'web']);
        }

        $superAdmin = Role::query()->firstOrCreate(['name' => RoleKey::SuperAdmin->value, 'guard_name' => 'web']);
        $superAdmin->syncPermissions(PermissionKey::cases());

        $userRole = Role::query()->firstOrCreate(['name' => RoleKey::User->value, 'guard_name' => 'web']);
        $userRole->syncPermissions([
            PermissionKey::ViewDashboard->value,
            PermissionKey::ViewClients->value,
            PermissionKey::ManageClients->value,
            PermissionKey::ViewProjects->value,
            PermissionKey::ManageProjects->value,
            PermissionKey::ViewInvoices->value,
            PermissionKey::ManageInvoices->value,
            PermissionKey::ViewTransactions->value,
            PermissionKey::ManageTransactions->value,
            PermissionKey::ViewLeads->value,
            PermissionKey::ManageLeads->value,
            PermissionKey::ViewIssues->value,
            PermissionKey::ManageIssues->value,
            PermissionKey::ViewKpo->value,
            PermissionKey::ManageCategories->value,
            PermissionKey::ManageTaxYears->value,
            PermissionKey::ManageSettings->value,
        ]);
    }
}
