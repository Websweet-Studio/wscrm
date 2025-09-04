<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Bank>
 */
class BankFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $accountNumber = fake()->numerify('##########');
        $accountName = fake()->company();
        $bankCode = fake()->unique()->regexify('[A-Z]{3,8}'); // Generate unique bank code
        $bankName = 'Bank ' . fake()->company();

        return [
            'bank_name' => $bankName,
            'bank_code' => $bankCode,
            'account_number' => $accountNumber,
            'account_name' => $accountName,
            'branch' => fake()->optional()->city(),
            'swift_code' => fake()->optional()->regexify('[A-Z]{4}ID[A-Z0-9]{2}'),
            'description' => fake()->optional()->sentence(),
            'is_active' => fake()->boolean(85), // 85% chance to be active
            'admin_fee' => fake()->randomFloat(2, 0, 5000), // IDR 0-5000
            'bank_type' => fake()->randomElement(['local', 'international']),
        ];
    }

    public function active(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_active' => true,
        ]);
    }

    public function inactive(): static
    {
        return $this->state(fn (array $attributes) => [
            'is_active' => false,
        ]);
    }

    public function local(): static
    {
        return $this->state(fn (array $attributes) => [
            'bank_type' => 'local',
        ]);
    }

    public function international(): static
    {
        return $this->state(fn (array $attributes) => [
            'bank_type' => 'international',
            'swift_code' => fake()->regexify('[A-Z]{4}ID[A-Z0-9]{2}'),
        ]);
    }
}
