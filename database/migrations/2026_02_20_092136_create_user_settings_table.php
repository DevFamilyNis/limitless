<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('user_settings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')
                ->constrained()
                ->cascadeOnDelete();
            $table->string('display_name');
            $table->string('address')->nullable();
            $table->string('pib')->nullable();
            $table->string('mb')->nullable();
            $table->string('bank_account')->nullable();
            $table->string('default_currency')->default('RSD');
            $table->timestamps();
        });

        $defaultUserId = DB::table('users')
            ->where('email', 'dev.family.nis@gmail.com')
            ->value('id');

        if ($defaultUserId) {
            DB::table('user_settings')->insert([
                'user_id' => $defaultUserId,
                'display_name' => 'Dev-Family',
                'address' => 'Branka Radičevića 26a',
                'pib' => '113101530',
                'mb' => '66579484',
                'bank_account' => '160-6000001451121-46',
                'default_currency' => 'RSD',
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('user_settings');
    }
};
