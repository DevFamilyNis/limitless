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
        Schema::create('client_project_rates', function (Blueprint $table) {
            $table->id();
            $table->foreignId('client_id')
                ->constrained('clients')
                ->cascadeOnDelete();
            $table->foreignId('project_id')
                ->constrained('projects')
                ->cascadeOnDelete();
            $table->foreignId('billing_period_id')
                ->constrained('billing_periods')
                ->restrictOnDelete();
            $table->decimal('price_amount', 12, 2);
            $table->string('currency')->default('RSD');
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index(['client_id', 'project_id']);
            $table->index(['project_id', 'is_active']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('client_project_rates');
    }
};
