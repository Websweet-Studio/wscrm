<?php

namespace Database\Seeders;

use App\Models\Customer;
use App\Models\Order;
use App\Models\OrderItem;
use Illuminate\Database\Seeder;

class OrderSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Get existing customers
        $customers = Customer::all();

        if ($customers->isEmpty()) {
            $this->command->warn('No customers found. Please run CustomerSeeder first.');

            return;
        }

        $this->command->info('Creating realistic order scenarios...');

        // Create orders for existing customers
        $customers->each(function ($customer) {
            // Create 1-3 orders per customer with realistic scenarios
            $orderCount = fake()->numberBetween(1, 3);

            for ($i = 0; $i < $orderCount; $i++) {
                $this->createRealisticOrder($customer);
            }
        });

        $this->command->info('Orders and OrderItems created successfully!');
    }

    private function createRealisticOrder(Customer $customer): void
    {
        // Create different types of realistic orders
        $orderScenarios = [
            'hosting_with_domain',
            'domain_only',
            'hosting_with_maintenance',
            'web_development_package',
            'app_development_package',
            'maintenance_only',
        ];

        $scenario = fake()->randomElement($orderScenarios);

        switch ($scenario) {
            case 'hosting_with_domain':
                $this->createHostingWithDomainOrder($customer);
                break;
            case 'domain_only':
                $this->createDomainOnlyOrder($customer);
                break;
            case 'hosting_with_maintenance':
                $this->createHostingWithMaintenanceOrder($customer);
                break;
            case 'web_development_package':
                $this->createWebDevelopmentPackage($customer);
                break;
            case 'app_development_package':
                $this->createAppDevelopmentPackage($customer);
                break;
            case 'maintenance_only':
                $this->createMaintenanceOnlyOrder($customer);
                break;
        }
    }

    private function createHostingWithDomainOrder(Customer $customer): void
    {
        $order = Order::factory()
            ->forCustomer($customer)
            ->hostingOrder()
            ->create();

        // Add hosting plan
        OrderItem::factory()
            ->for($order)
            ->hosting()
            ->create();

        // Add domain registration
        OrderItem::factory()
            ->for($order)
            ->domain()
            ->create();

        // Recalculate total
        $this->updateOrderTotal($order);
    }

    private function createDomainOnlyOrder(Customer $customer): void
    {
        $order = Order::factory()
            ->forCustomer($customer)
            ->domainOrder()
            ->create();

        // Add domain registration
        OrderItem::factory()
            ->for($order)
            ->domain()
            ->create();

        $this->updateOrderTotal($order);
    }

    private function createHostingWithMaintenanceOrder(Customer $customer): void
    {
        $order = Order::factory()
            ->forCustomer($customer)
            ->hostingOrder()
            ->create();

        // Add hosting plan
        OrderItem::factory()
            ->for($order)
            ->hosting()
            ->create();

        // Add maintenance service
        OrderItem::factory()
            ->for($order)
            ->maintenance()
            ->create();

        $this->updateOrderTotal($order);
    }

    private function createWebDevelopmentPackage(Customer $customer): void
    {
        $order = Order::factory()
            ->forCustomer($customer)
            ->serviceOrder()
            ->create();

        // Add web development
        OrderItem::factory()
            ->for($order)
            ->state(['item_type' => 'web'])
            ->create();

        // Add hosting
        OrderItem::factory()
            ->for($order)
            ->hosting()
            ->create();

        // Maybe add domain
        if (fake()->boolean(80)) {
            OrderItem::factory()
                ->for($order)
                ->domain()
                ->create();
        }

        $this->updateOrderTotal($order);
    }

    private function createAppDevelopmentPackage(Customer $customer): void
    {
        $order = Order::factory()
            ->forCustomer($customer)
            ->serviceOrder()
            ->create();

        // Add app development
        OrderItem::factory()
            ->for($order)
            ->state(['item_type' => 'app'])
            ->create();

        // Add hosting for backend
        OrderItem::factory()
            ->for($order)
            ->hosting()
            ->create();

        $this->updateOrderTotal($order);
    }

    private function createMaintenanceOnlyOrder(Customer $customer): void
    {
        $order = Order::factory()
            ->forCustomer($customer)
            ->serviceOrder()
            ->state(['billing_cycle' => 'monthly'])
            ->create();

        // Add maintenance service
        OrderItem::factory()
            ->for($order)
            ->maintenance()
            ->create();

        $this->updateOrderTotal($order);
    }

    private function updateOrderTotal(Order $order): void
    {
        $total = $order->orderItems()->sum('price');
        $order->update(['total_amount' => $total]);
    }
}
