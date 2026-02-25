<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (! Schema::hasTable('users')) {
            return;
        }

        DB::table('users')
            ->where('email', 'dev.famil.nis@gmail.com')
            ->update([
                'email' => 'dev.family.nis@gmail.com',
                'updated_at' => now(),
            ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (! Schema::hasTable('users')) {
            return;
        }

        DB::table('users')
            ->where('email', 'dev.family.nis@gmail.com')
            ->update([
                'email' => 'dev.famil.nis@gmail.com',
                'updated_at' => now(),
            ]);
    }
};
