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
        if (!Schema::hasTable('tasks')) {
            Schema::create('tasks', function (Blueprint $table) {
                $table->id();
                $table->string('title');
                $table->text('description')->nullable();
                $table->enum('status', ['todo', 'in_progress', 'done', 'cancelled'])->default('todo');
                $table->enum('priority', ['low', 'medium', 'high'])->default('medium');
                $table->date('due_date')->nullable();
                $table->foreignId('assigned_user_id')->nullable()->constrained('users')->onDelete('set null');
                $table->string('assigned_department')->nullable();
                $table->foreignId('created_by_user_id')->constrained('users')->onDelete('cascade');
                $table->timestamps();

                $table->index(['assigned_user_id', 'assigned_department']);
                $table->index(['status', 'due_date']);
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tasks');
    }
};

