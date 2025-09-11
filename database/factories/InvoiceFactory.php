<?php

namespace Database\Factories;

use App\Models\Customer;
use App\Models\Invoice;
use App\Models\Order;
use Illuminate\Database\Eloquent\Factories\Factory;

class InvoiceFactory extends Factory
{
    protected $model = Invoice::class;

    public function definition(): array
    {
        $statuses = ['draft', 'sent', 'paid', 'overdue', 'cancelled'];
        $totalAmount = fake()->randomFloat(2, 100000, 3000000); // IDR 100k-3M

        return [
            'customer_id' => Customer::factory(),
            'order_id' => Order::factory(),
            'invoice_number' => $this->generateInvoiceNumber(),
            'amount' => $totalAmount,
            'status' => fake()->randomElement($statuses),
            'due_date' => fake()->dateTimeBetween('now', '+1 month'),
            'paid_at' => fake()->boolean(60) ? fake()->dateTimeBetween('-1 month', 'now') : null,
        ];
    }

    private function generateInvoiceNumber(): string
    {
        $prefix = 'INV';
        $year = date('Y');
        $month = str_pad(date('m'), 2, '0', STR_PAD_LEFT);
        
        // Generate unique sequence by checking existing invoice numbers
        do {
            $sequence = str_pad(fake()->numberBetween(1, 99999), 4, '0', STR_PAD_LEFT);
            $invoiceNumber = "{$prefix}/{$year}/{$month}/{$sequence}";
        } while (Invoice::where('invoice_number', $invoiceNumber)->exists());

        return $invoiceNumber;
    }

    public function paid(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'paid',
            'paid_at' => fake()->dateTimeBetween('-1 month', 'now'),
        ]);
    }

    public function sent(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'sent',
            'paid_at' => null,
        ]);
    }

    public function overdue(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'overdue',
            'due_date' => fake()->dateTimeBetween('-1 month', '-1 day'),
            'paid_at' => null,
        ]);
    }

    public function forCustomer(Customer $customer): static
    {
        return $this->state(fn (array $attributes) => [
            'customer_id' => $customer->id,
        ]);
    }
}
