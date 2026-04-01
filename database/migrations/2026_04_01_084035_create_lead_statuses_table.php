<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('lead_statuses', function (Blueprint $table) {
            $table->id();
            $table->string('key')->unique();
            $table->string('name');
        });

        DB::table('lead_statuses')->insert([
            ['key' => 'new', 'name' => 'Novi'],
            ['key' => 'contacted', 'name' => 'Kontaktiran'],
            ['key' => 'responded', 'name' => 'Odgovorio'],
            ['key' => 'not_available', 'name' => 'Nedostupan'],
            ['key' => 'interested', 'name' => 'Zainteresovan'],
            ['key' => 'not_interested', 'name' => 'Nezainteresovan'],
            ['key' => 'converted', 'name' => 'Konvertovan'],
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('lead_statuses');
    }
};
