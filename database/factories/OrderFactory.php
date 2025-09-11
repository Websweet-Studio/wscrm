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
        $orderTypes = ['hosting', 'domain', 'service'];
        $statuses = ['pending', 'processing', 'active', 'suspended', 'expired', 'cancelled', 'terminated'];
        $billingCycles = ['onetime', 'monthly', 'quarterly', 'semi_annually', 'annually'];

        $orderType = fake()->randomElement($orderTypes);

        // Generate domain name 70% of the time
        $hasDomain = fake()->boolean(70);
        $domainName = $hasDomain ? fake()->domainName() : null;

        return [
            'customer_id' => Customer::factory(),
            'order_type' => $orderType,
            'service_type' => fake()->randomElement(['hosting', 'domain']), // Keep for legacy compatibility
            'domain_name' => $domainName,
            'total_amount' => $this->generateTotalAmount($orderType),
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
            case 'service':
                return fake()->randomFloat(2, 500000, 5000000); // IDR 500k-5M
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
            'order_type' => 'hosting',
            'service_type' => 'hosting',
            'domain_name' => fake()->domainName(),
            'total_amount' => fake()->randomFloat(2, 200000, 2000000),
        ]);
    }

    public function domainOrder(): static
    {
        return $this->state(fn (array $attributes) => [
            'order_type' => 'domain',
            'service_type' => 'domain',
            'domain_name' => fake()->domainName(),
            'total_amount' => fake()->randomFloat(2, 100000, 500000),
        ]);
    }

    public function serviceOrder(): static
    {
        return $this->state(fn (array $attributes) => [
            'order_type' => 'service',
            'service_type' => 'hosting',
            'domain_name' => fake()->boolean(80) ? fake()->domainName() : null,
            'total_amount' => fake()->randomFloat(2, 500000, 5000000),
        ]);
    }

    public function forCustomer(Customer $customer): static
    {
        return $this->state(fn (array $attributes) => [
            'customer_id' => $customer->id,
        ]);
    }
}
