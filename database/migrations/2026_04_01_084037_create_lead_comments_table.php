<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('lead_comments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('lead_id')
                ->constrained('leads')
                ->cascadeOnDelete();
            $table->foreignId('author_id')
                ->constrained('users')
                ->restrictOnDelete();
            $table->foreignId('lead_status_id')
                ->nullable()
                ->constrained('lead_statuses')
                ->nullOnDelete();
            $table->string('event_type');
            $table->string('contact_method')->nullable();
            $table->string('outcome')->nullable();
            $table->text('body');
            $table->dateTime('contacted_at')->nullable();
            $table->dateTime('responded_at')->nullable();
            $table->dateTime('next_follow_up_at')->nullable();
            $table->timestamps();

            $table->index(['lead_id', 'contacted_at']);
            $table->index('outcome');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('lead_comments');
    }
};
