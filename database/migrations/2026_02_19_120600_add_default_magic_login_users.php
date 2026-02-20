<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $now = now();

        DB::table('users')->upsert(
            [
                [
                    'name' => 'Igor Mitrinović',
                    'email' => 'dev.famil.nis@gmail.com',
                    'email_verified_at' => $now,
                    'password' => Hash::make(Str::random(40)),
                    'created_at' => $now,
                    'updated_at' => $now,
                ],
                [
                    'name' => 'Stevan Đorđević',
                    'email' => 'stevan.dev.family@gmail.com',
                    'email_verified_at' => $now,
                    'password' => Hash::make(Str::random(40)),
                    'created_at' => $now,
                    'updated_at' => $now,
                ],
            ],
            ['email'],
            ['name', 'email_verified_at', 'password', 'updated_at']
        );
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::table('users')
            ->whereIn('email', [
                'dev.famil.nis@gmail.com',
                'stevan.dev.family@gmail.com',
            ])
            ->delete();
    }
};
