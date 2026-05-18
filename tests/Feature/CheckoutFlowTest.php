<?php

use App\Models\Order;
use App\Models\Product;

test('customer can place a cod order from cart', function () {
    $product = Product::factory()->create(['price' => 150000, 'stock' => 3]);

    $response = $this
        ->withSession(['cart' => [$product->id => 2]])
        ->post(route('checkout.store'), [
            'customer_name' => 'Nguyen Van A',
            'customer_email' => 'customer@example.com',
            'customer_phone' => '0900000000',
            'customer_address' => '123 Nguyen Hue',
            'payment_method' => 'cod',
        ]);

    $order = Order::firstOrFail();

    $response->assertRedirect(route('storefront.orders.show', ['order' => $order->tracking_code]));

    expect($order->payment_method)->toBe('cod')
        ->and($order->payment_status)->toBe('unpaid')
        ->and($order->items)->toHaveCount(1)
        ->and($product->fresh()->stock)->toBe(1);
});

test('customer is redirected to vnpay checkout after choosing online payment', function () {
    config()->set('services.vnpay.url', 'https://sandbox.vnpayment.vn/paymentv2/vpcpay.html');
    config()->set('services.vnpay.tmn_code', 'DEMO');
    config()->set('services.vnpay.hash_secret', 'secret');
    config()->set('services.vnpay.return_url', route('payment.callback'));

    $product = Product::factory()->create(['stock' => 2]);

    $response = $this
        ->withSession(['cart' => [$product->id => 1]])
        ->post(route('checkout.store'), [
            'customer_name' => 'Nguyen Van B',
            'customer_email' => 'customer-b@example.com',
            'customer_phone' => '0911111111',
            'customer_address' => '456 Le Loi',
            'payment_method' => 'vnpay',
        ]);

    $order = Order::firstOrFail();

    $response->assertRedirect(route('payment.checkout', ['order' => $order->tracking_code]));
});

test('checkout rejects insufficient stock', function () {
    $product = Product::factory()->create(['stock' => 1]);

    $this
        ->withSession(['cart' => [$product->id => 2]])
        ->post(route('checkout.store'), [
            'customer_name' => 'Nguyen Van C',
            'customer_email' => 'customer-c@example.com',
            'customer_phone' => '0922222222',
            'customer_address' => '789 Tran Hung Dao',
            'payment_method' => 'cod',
        ])
        ->assertSessionHasErrors('cart');
});
