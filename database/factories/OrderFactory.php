<?php

namespace Database\Factories;

use App\Models\Customer;
use App\Models\Order;
use Illuminate\Database\Eloquent\Factories\Factory;

class OrderFactory extends Factory
{
    protected $model = Order::class;

    public function definition(): array
    {
        $orderTypes = ['hosting', 'domain', 'mixed'];
        $statuses = ['pending', 'processing', 'completed', 'cancelled'];
        $billingCycles = ['monthly', 'quarterly', 'semi_annually', 'annually'];

        $orderType = fake()->randomElement($orderTypes);
        $totalAmount = $this->generateTotalAmount($orderType);

        return [
            'customer_id' => Customer::factory(),
            'order_type' => $orderType,
            'total_amount' => $totalAmount,
            'status' => fake()->randomElement($statuses),
            'billing_cycle' => fake()->randomElement($billingCycles),
        ];
    }

    private function generateTotalAmount(string $orderType): float
    {
        switch ($orderType) {
            case 'domain':
                return fake()->randomFloat(2, 100000, 500000); // IDR 100k-500k
            case 'hosting':
                return fake()->randomFloat(2, 200000, 2000000); // IDR 200k-2M
            case 'mixed':
                return fake()->randomFloat(2, 300000, 3000000); // IDR 300k-3M
            default:
                return fake()->randomFloat(2, 100000, 1000000);
        }
    }

    public function pending(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'pending',
        ]);
    }

    public function completed(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'completed',
        ]);
    }

    public function hostingOrder(): static
    {
        return $this->state(fn (array $attributes) => [
            'order_type' => 'hosting',
            'total_amount' => fake()->randomFloat(2, 200000, 2000000),
        ]);
    }

    public function domainOrder(): static
    {
        return $this->state(fn (array $attributes) => [
            'order_type' => 'domain',
            'total_amount' => fake()->randomFloat(2, 100000, 500000),
        ]);
    }

    public function forCustomer(Customer $customer): static
    {
        return $this->state(fn (array $attributes) => [
            'customer_id' => $customer->id,
        ]);
    }
}
