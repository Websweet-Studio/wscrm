<?php

namespace Database\Seeders;

use App\Models\Employee;
use App\Models\Task;
use App\Models\User;
use Illuminate\Database\Seeder;

class TaskSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $users = User::all(['id', 'name']);
        $departments = Employee::distinct()->pluck('department')->filter()->values()->all();

        if ($users->isEmpty()) {
            $this->command->warn('No users found. Please seed users first.');
            return;
        }

        $statuses = ['todo', 'in_progress', 'done', 'cancelled'];
        $priorities = ['low', 'medium', 'high'];

        // Create 20 tasks with mixed assignments
        for ($i = 0; $i < 20; $i++) {
            $creator = $users->random();
            $assignToUser = fake()->boolean(60); // 60% to specific user
            $assignToDept = !$assignToUser && !empty($departments) && fake()->boolean(70); // 70% of remaining to department if available

            $data = [
                'title' => fake()->sentence(6),
                'description' => fake()->boolean(70) ? fake()->paragraphs(2, true) : null,
                'status' => fake()->randomElement($statuses),
                'priority' => fake()->randomElement($priorities),
                'due_date' => fake()->boolean(60) ? fake()->dateTimeBetween('now', '+30 days')->format('Y-m-d') : null,
                'created_by_user_id' => $creator->id,
            ];

            if ($assignToUser) {
                $data['assigned_user_id'] = $users->random()->id;
            } elseif ($assignToDept) {
                $data['assigned_department'] = fake()->randomElement($departments);
            } else {
                // Default to self if neither provided
                $data['assigned_user_id'] = $creator->id;
            }

            Task::create($data);
        }

        $this->command->info('Tasks seeded successfully!');
    }
}

