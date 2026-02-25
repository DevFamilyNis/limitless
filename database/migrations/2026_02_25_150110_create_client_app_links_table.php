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
        Schema::create('client_app_links', function (Blueprint $table) {
            $table->id();
            $table->foreignId('client_id')
                ->constrained('clients')
                ->cascadeOnDelete();
            $table->string('label')->nullable();
            $table->string('url');
            $table->timestamps();

            $table->index(['client_id', 'id']);
        });

        if (Schema::hasTable('clients') && Schema::hasColumn('clients', 'app_link')) {
            DB::table('clients')
                ->select('id', 'app_link')
                ->whereNotNull('app_link')
                ->where('app_link', '!=', '')
                ->orderBy('id')
                ->lazyById()
                ->each(function (object $client): void {
                    DB::table('client_app_links')->insert([
                        'client_id' => (int) $client->id,
                        'label' => null,
                        'url' => (string) $client->app_link,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('client_app_links');
    }
};
