<?php

namespace Database\Seeders;

use App\Enums\AppSettingKey;
use App\Enums\RoleKey;
use App\Models\AppSetting;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminUserSeeder extends Seeder
{
    public function run(): void
    {
        $admin = User::query()->firstOrCreate(
            ['email' => 'dev.family.nis@gmail.com'],
            [
                'name' => 'Dev Family',
                'password' => Hash::make(config('app.admin_password', 'change-me-in-production')),
                'email_verified_at' => now(),
            ]
        );

        $admin->syncRoles([RoleKey::SuperAdmin->value]);

        AppSetting::updateOrCreate(
            ['key' => AppSettingKey::OfficialSignerUserId->value],
            ['value' => (string) $admin->id]
        );
    }
}
