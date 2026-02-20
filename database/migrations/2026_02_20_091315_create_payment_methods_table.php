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
        Schema::create('payment_methods', function (Blueprint $table) {
            $table->id();
            $table->string('key')->unique();
            $table->string('name');
            $table->timestamps();
        });

        $now = now();

        DB::table('payment_methods')->insert([
            [
                'key' => 'bank',
                'name' => 'Banka',
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'key' => 'cash',
                'name' => 'Gotovina',
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'key' => 'card',
                'name' => 'Kartica',
                'created_at' => $now,
                'updated_at' => $now,
            ],
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payment_methods');
    }
};
