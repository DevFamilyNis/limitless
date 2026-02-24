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
        Schema::create('kpo_report_rows', function (Blueprint $table) {
            $table->id();

            $table->foreignId('kpo_report_id')
                ->constrained('kpo_reports')
                ->cascadeOnDelete();

            $table->foreignId('invoice_id')
                ->constrained('invoices')
                ->restrictOnDelete();

            $table->date('entry_date');
            $table->string('entry_description');
            $table->decimal('products_amount', 15, 2)->default(0);
            $table->decimal('services_amount', 15, 2)->default(0);
            $table->decimal('activity_amount', 15, 2)->default(0);
            $table->unsignedInteger('row_no');

            $table->timestamps();

            $table->unique(['kpo_report_id', 'invoice_id']);
            $table->unique(['kpo_report_id', 'row_no']);
            $table->index(['kpo_report_id', 'entry_date']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('kpo_report_rows');
    }
};
