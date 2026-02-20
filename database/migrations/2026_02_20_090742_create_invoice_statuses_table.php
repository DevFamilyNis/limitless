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
        Schema::create('invoice_statuses', function (Blueprint $table) {
            $table->id();
            $table->string('key')->unique();
            $table->string('name');
            $table->timestamps();
        });

        $now = now();

        DB::table('invoice_statuses')->insert([
            [
                'key' => 'draft',
                'name' => 'Kreirana',
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'key' => 'sent',
                'name' => 'Poslata',
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'key' => 'paid',
                'name' => 'Plaćena',
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'key' => 'canceled',
                'name' => 'Otkazana',
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
        Schema::dropIfExists('invoice_statuses');
    }
};
