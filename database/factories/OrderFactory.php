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
        $serviceTypes = ['hosting', 'domain'];
        $statuses = ['pending', 'processing', 'active', 'suspended', 'expired', 'cancelled', 'terminated'];
        $billingCycles = ['onetime', 'monthly', 'quarterly', 'semi_annually', 'annually'];

        $serviceType = fake()->randomElement($serviceTypes);
        $totalAmount = $this->generateTotalAmount($serviceType);

        return [
            'customer_id' => Customer::factory(),
            'service_type' => $serviceType,
            'total_amount' => $totalAmount,
            'status' => fake()->randomElement($statuses),
            'billing_cycle' => fake()->randomElement($billingCycles),
            'domain_name' => $serviceType === 'domain' ? fake()->domainName() : null,
        ];
    }

    private function generateTotalAmount(string $serviceType): float
    {
        switch ($serviceType) {
            case 'domain':
                return fake()->randomFloat(2, 100000, 500000); // IDR 100k-500k
            case 'hosting':
                return fake()->randomFloat(2, 200000, 2000000); // IDR 200k-2M
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

    public function active(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'active',
        ]);
    }

    public function hostingOrder(): static
    {
        return $this->state(fn (array $attributes) => [
            'service_type' => 'hosting',
            'total_amount' => fake()->randomFloat(2, 200000, 2000000),
        ]);
    }

    public function domainOrder(): static
    {
        return $this->state(fn (array $attributes) => [
            'service_type' => 'domain',
            'domain_name' => fake()->domainName(),
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
