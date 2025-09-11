<?php

namespace Database\Factories;

use App\Models\DomainPrice;
use App\Models\HostingPlan;
use App\Models\Order;
use App\Models\OrderItem;
use Illuminate\Database\Eloquent\Factories\Factory;

class OrderItemFactory extends Factory
{
    protected $model = OrderItem::class;

    public function definition(): array
    {
        $itemTypes = ['hosting', 'domain', 'service', 'app', 'web', 'maintenance'];
        $itemType = fake()->randomElement($itemTypes);

        $data = [
            'order_id' => Order::factory(),
            'item_type' => $itemType,
            'quantity' => 1, // Always 1 based on new structure
            'domain_name' => null, // Domain name is now at order level
        ];

        // Set item_id and price based on item type
        switch ($itemType) {
            case 'domain':
                $existingDomainPrice = DomainPrice::where('is_active', true)->inRandomOrder()->first();
                if ($existingDomainPrice) {
                    $data['item_id'] = $existingDomainPrice->id;
                    $data['price'] = $existingDomainPrice->selling_price;
                } else {
                    $domainPrice = DomainPrice::factory()->active()->create();
                    $data['item_id'] = $domainPrice->id;
                    $data['price'] = $domainPrice->selling_price;
                }
                break;

            case 'hosting':
                $existingHostingPlan = HostingPlan::where('is_active', true)->inRandomOrder()->first();
                if ($existingHostingPlan) {
                    $data['item_id'] = $existingHostingPlan->id;
                    $data['price'] = $existingHostingPlan->selling_price;
                } else {
                    $hostingPlan = HostingPlan::factory()->active()->create();
                    $data['item_id'] = $hostingPlan->id;
                    $data['price'] = $hostingPlan->selling_price;
                }
                break;

            case 'service':
                $data['item_id'] = fake()->numberBetween(1, 10);
                $data['price'] = fake()->randomFloat(2, 500000, 2000000);
                break;

            case 'app':
                $data['item_id'] = fake()->numberBetween(1, 3);
                $data['price'] = fake()->randomFloat(2, 1500000, 2500000);
                break;

            case 'web':
                $data['item_id'] = fake()->numberBetween(1, 3);
                $data['price'] = fake()->randomFloat(2, 500000, 2000000);
                break;

            case 'maintenance':
                $data['item_id'] = fake()->numberBetween(1, 3);
                $data['price'] = fake()->randomFloat(2, 200000, 1000000);
                break;

            default:
                $data['item_id'] = 1;
                $data['price'] = fake()->randomFloat(2, 100000, 500000);
        }

        return $data;
    }

    public function domain(): static
    {
        return $this->state(fn (array $attributes) => [
            'item_type' => 'domain',
            'quantity' => 1,
            'domain_name' => null,
            'price' => fake()->randomFloat(2, 100000, 500000),
        ]);
    }

    public function hosting(): static
    {
        return $this->state(fn (array $attributes) => [
            'item_type' => 'hosting',
            'quantity' => 1,
            'domain_name' => null,
            'price' => fake()->randomFloat(2, 200000, 2000000),
        ]);
    }

    public function service(): static
    {
        return $this->state(fn (array $attributes) => [
            'item_type' => 'service',
            'quantity' => 1,
            'domain_name' => null,
            'price' => fake()->randomFloat(2, 500000, 2000000),
        ]);
    }

    public function maintenance(): static
    {
        return $this->state(fn (array $attributes) => [
            'item_type' => 'maintenance',
            'quantity' => 1,
            'domain_name' => null,
            'price' => fake()->randomFloat(2, 200000, 1000000),
        ]);
    }
}
