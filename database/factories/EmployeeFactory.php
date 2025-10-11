<?php

namespace Database\Factories;

use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Employee>
 */
class EmployeeFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $departments = ['IT', 'HR', 'Finance', 'Marketing', 'Sales', 'Operations', 'Customer Service'];
        $positions = [
            'IT' => ['Software Developer', 'IT Support', 'System Administrator', 'Network Engineer'],
            'HR' => ['HR Manager', 'Recruiter', 'HR Staff', 'Training Coordinator'],
            'Finance' => ['Finance Manager', 'Accountant', 'Financial Analyst', 'Bookkeeper'],
            'Marketing' => ['Marketing Manager', 'Digital Marketer', 'Content Creator', 'Brand Manager'],
            'Sales' => ['Sales Manager', 'Sales Representative', 'Account Manager', 'Business Development'],
            'Operations' => ['Operations Manager', 'Logistics Coordinator', 'Quality Control', 'Production Supervisor'],
            'Customer Service' => ['Customer Service Manager', 'Support Agent', 'Customer Success', 'Call Center Agent'],
        ];

        $department = fake()->randomElement($departments);
        $position = fake()->randomElement($positions[$department]);

        return [
            'user_id' => User::factory(),
            'nik' => 'EMP'.fake()->unique()->numerify('######'),
            'position' => $position,
            'department' => $department,
            'phone' => fake()->phoneNumber(),
            'address' => fake()->address(),
            'hire_date' => fake()->dateTimeBetween('-5 years', 'now'),
            'salary' => fake()->numberBetween(3000000, 15000000),
            'status' => fake()->randomElement(['active', 'active', 'active', 'inactive']),
            'notes' => fake()->optional(0.3)->sentence(),
        ];
    }
}
