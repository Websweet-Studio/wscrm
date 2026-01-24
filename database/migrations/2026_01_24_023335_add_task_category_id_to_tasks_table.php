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
        Schema::table('tasks', function (Blueprint $table) {
            if (!Schema::hasColumn('tasks', 'task_category_id')) {
                $table->foreignId('task_category_id')->nullable()->after('title')->constrained('task_categories')->onDelete('set null');
            }
        });

        // Migrate existing string categories to new table
        if (Schema::hasColumn('tasks', 'category')) {
            $tasks = \Illuminate\Support\Facades\DB::table('tasks')->whereNotNull('category')->get();
            foreach ($tasks as $task) {
                if ($task->category) {
                    $categoryId = \Illuminate\Support\Facades\DB::table('task_categories')->insertGetId([
                        'name' => $task->category,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                    
                    \Illuminate\Support\Facades\DB::table('tasks')
                        ->where('id', $task->id)
                        ->update(['task_category_id' => $categoryId]);
                }
            }

            Schema::table('tasks', function (Blueprint $table) {
                $table->dropColumn('category');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tasks', function (Blueprint $table) {
            $table->string('category')->nullable()->after('title');
        });

        // Restore data
        if (Schema::hasColumn('tasks', 'task_category_id')) {
            $tasks = \Illuminate\Support\Facades\DB::table('tasks')->whereNotNull('task_category_id')->get();
            foreach ($tasks as $task) {
                $category = \Illuminate\Support\Facades\DB::table('task_categories')->find($task->task_category_id);
                if ($category) {
                    \Illuminate\Support\Facades\DB::table('tasks')
                        ->where('id', $task->id)
                        ->update(['category' => $category->name]);
                }
            }

            Schema::table('tasks', function (Blueprint $table) {
                $table->dropForeign(['task_category_id']);
                $table->dropColumn('task_category_id');
            });
        }
    }
};
