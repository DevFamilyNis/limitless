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
        Schema::create('issues', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_id')
                ->constrained('projects')
                ->restrictOnDelete();
            $table->foreignId('client_id')
                ->nullable()
                ->constrained('clients')
                ->nullOnDelete();
            $table->foreignId('client_contact_id')
                ->nullable()
                ->constrained('client_contacts')
                ->nullOnDelete();
            $table->foreignId('status_id')
                ->constrained('issue_statuses')
                ->restrictOnDelete();
            $table->foreignId('priority_id')
                ->constrained('issue_priorities')
                ->restrictOnDelete();
            $table->foreignId('category_id')
                ->constrained('issue_categories')
                ->restrictOnDelete();
            $table->foreignId('author_id')
                ->constrained('users')
                ->restrictOnDelete();
            $table->foreignId('assignee_id')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();
            $table->string('title');
            $table->text('description')->nullable();
            $table->date('due_date')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();

            $table->index(['project_id', 'status_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('issues');
    }
};
