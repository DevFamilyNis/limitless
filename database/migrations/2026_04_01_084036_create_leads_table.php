<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('leads', function (Blueprint $table) {
            $table->id();
            $table->foreignId('lead_status_id')
                ->constrained('lead_statuses')
                ->restrictOnDelete();
            $table->string('company_name');
            $table->string('email')->nullable();
            $table->string('phone')->nullable();
            $table->dateTime('last_contacted_at')->nullable();
            $table->string('last_contact_method')->nullable();
            $table->dateTime('last_response_at')->nullable();
            $table->dateTime('converted_at')->nullable();
            $table->timestamps();

            $table->index('company_name');
            $table->index(['lead_status_id', 'last_contacted_at']);
            $table->index('converted_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('leads');
    }
};
