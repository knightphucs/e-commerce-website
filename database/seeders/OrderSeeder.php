<?php

namespace Database\Seeders;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\User;
use Illuminate\Database\Seeder;

class OrderSeeder extends Seeder
{
    public function run(): void
    {
        $products = Product::all();
        $customer = User::where('role', 'customer')->first();

        if ($products->isEmpty()) {
            return;
        }

        $statuses = ['pending', 'processing', 'shipped', 'delivered', 'cancelled'];
        $paymentMethods = ['cod', 'vnpay'];

        $customers = [
            ['name' => 'Nguyễn Văn An', 'email' => 'an.nguyen@gmail.com', 'phone' => '0901234567', 'address' => '123 Lê Lợi, Q1, TP.HCM'],
            ['name' => 'Trần Thị Bình', 'email' => 'binh.tran@gmail.com', 'phone' => '0912345678', 'address' => '456 Nguyễn Huệ, Q1, TP.HCM'],
            ['name' => 'Lê Văn Cường', 'email' => 'cuong.le@gmail.com', 'phone' => '0923456789', 'address' => '789 Hai Bà Trưng, Q3, TP.HCM'],
            ['name' => 'Phạm Thị Dung', 'email' => 'dung.pham@gmail.com', 'phone' => '0934567890', 'address' => '321 Đinh Tiên Hoàng, Q1, TP.HCM'],
            ['name' => 'Hoàng Văn Em', 'email' => 'em.hoang@gmail.com', 'phone' => '0945678901', 'address' => '654 Võ Thị Sáu, Q3, TP.HCM'],
        ];

        foreach (range(1, 12) as $i) {
            $customerData = $customers[($i - 1) % count($customers)];
            $status = $statuses[($i - 1) % count($statuses)];
            $paymentMethod = $paymentMethods[($i - 1) % 2];
            $paymentStatus = $status === 'delivered' ? 'paid' : 'unpaid';

            $selectedProducts = $products->random(min(rand(1, 3), $products->count()));
            $subtotal = 0;

            $order = Order::create([
                'user_id' => $customer?->id,
                'customer_name' => $customerData['name'],
                'customer_email' => $customerData['email'],
                'customer_phone' => $customerData['phone'],
                'customer_address' => $customerData['address'],
                'status' => $status,
                'subtotal' => 0,
                'total' => 0,
                'payment_method' => $paymentMethod,
                'payment_status' => $paymentStatus,
                'created_at' => now()->subDays(rand(0, 60)),
            ]);

            foreach ($selectedProducts as $product) {
                $qty = rand(1, 3);
                $price = $product->price;
                $subtotal += $qty * $price;

                OrderItem::create([
                    'order_id' => $order->id,
                    'product_id' => $product->id,
                    'quantity' => $qty,
                    'unit_price' => $price,
                ]);
            }

            $order->update(['subtotal' => $subtotal, 'total' => $subtotal]);
        }
    }
}
