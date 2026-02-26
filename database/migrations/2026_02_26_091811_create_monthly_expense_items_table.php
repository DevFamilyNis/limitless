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
        Schema::create('monthly_expense_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')
                ->constrained('users')
                ->cascadeOnDelete();
            $table->foreignId('billing_period_id')
                ->constrained('billing_periods')
                ->restrictOnDelete();
            $table->string('title');
            $table->decimal('amount', 12, 2);
            $table->text('note')->nullable();
            $table->timestamps();

            $table->index(['user_id', 'billing_period_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('monthly_expense_items');
    }
};
