<?php

namespace Database\Factories;

use App\Models\Order;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Order>
 */
class OrderFactory extends Factory
{
    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $subtotal = fake()->randomFloat(2, 100000, 10000000);

        return [
            'user_id' => null,
            'customer_name' => fake()->name(),
            'customer_email' => fake()->safeEmail(),
            'customer_phone' => '09'.fake()->numerify('########'),
            'customer_address' => fake()->address(),
            'status' => 'pending',
            'subtotal' => $subtotal,
            'total' => $subtotal,
            'notes' => fake()->optional()->sentence(),
            'payment_method' => 'cod',
            'payment_status' => 'unpaid',
            'vnpay_txn_ref' => null,
        ];
    }

    public function pending(): static
    {
        return $this->state(fn (array $attributes) => ['status' => 'pending']);
    }

    public function delivered(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'delivered',
            'payment_status' => 'paid',
        ]);
    }

    public function cancelled(): static
    {
        return $this->state(fn (array $attributes) => ['status' => 'cancelled']);
    }

    public function vnpay(): static
    {
        return $this->state(fn (array $attributes) => ['payment_method' => 'vnpay']);
    }
}
