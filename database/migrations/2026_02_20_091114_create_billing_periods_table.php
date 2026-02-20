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
        Schema::create('billing_periods', function (Blueprint $table) {
            $table->id();
            $table->string('key')->unique();
            $table->string('name');
            $table->timestamps();
        });

        $now = now();

        DB::table('billing_periods')->insert([
            [
                'key' => 'monthly',
                'name' => 'Mesečno',
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'key' => 'yearly',
                'name' => 'Godišnje',
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'key' => 'one_time',
                'name' => 'Jednokratno',
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
        Schema::dropIfExists('billing_periods');
    }
};
