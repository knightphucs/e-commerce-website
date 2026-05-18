<?php

use App\Models\Order;

test('cod orders cannot start online checkout', function () {
    $order = Order::factory()->create(['tracking_code' => 'CODTRACK1234']);

    $this->get(route('payment.checkout', ['order' => $order->tracking_code]))
        ->assertForbidden();
});

test('unpaid vnpay orders can start online checkout', function () {
    config()->set('services.vnpay.url', 'https://sandbox.vnpayment.vn/paymentv2/vpcpay.html');
    config()->set('services.vnpay.tmn_code', 'DEMO');
    config()->set('services.vnpay.hash_secret', 'secret');
    config()->set('services.vnpay.return_url', route('payment.callback'));

    $order = Order::factory()->vnpay()->create(['tracking_code' => 'VNPAYTRACK12']);

    $this->get(route('payment.checkout', ['order' => $order->tracking_code]))
        ->assertRedirect();
});
