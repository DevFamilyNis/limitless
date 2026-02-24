<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('kpo_reports', function (Blueprint $table) {
            $table->id();

            $table->foreignId('user_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->year('year');
            $table->unsignedTinyInteger('month');
            $table->date('period_from');
            $table->date('period_to');

            $table->decimal('services_total', 15, 2)->default(0);
            $table->decimal('products_total', 15, 2)->default(0);
            $table->decimal('activity_total', 15, 2)->default(0);

            $table->char('currency', 3)->default('RSD');
            $table->timestamp('locked_at')->nullable();

            $table->timestamps();

            $table->unique(['user_id', 'year', 'month']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('kpo_reports');
    }
};
