<?php

use App\Mail\PaymentConfirmed;
use App\Models\Order;
use Illuminate\Support\Facades\Mail;

function signedVnpayParams(array $params, string $secret): array
{
    $sorted = $params;
    ksort($sorted);

    $signature = hash_hmac('sha512', http_build_query($sorted), $secret);

    return [...$params, 'vnp_SecureHash' => $signature];
}

beforeEach(function () {
    config()->set('services.vnpay.url', 'https://sandbox.vnpayment.vn/paymentv2/vpcpay.html');
    config()->set('services.vnpay.tmn_code', 'DEMO');
    config()->set('services.vnpay.hash_secret', 'secret');
    Mail::fake();
});

test('cod orders cannot start online checkout', function () {
    $order = Order::factory()->create(['tracking_code' => 'CODTRACK1234']);

    $this->get(route('payment.checkout', ['order' => $order->tracking_code]))
        ->assertForbidden();
});

test('unpaid vnpay orders can start online checkout', function () {
    $order = Order::factory()->vnpay()->create(['tracking_code' => 'VNPAYTRACK12']);

    $this->get(route('payment.checkout', ['order' => $order->tracking_code]))
        ->assertRedirect();
});

test('ipn confirms payment, marks order paid and advances status to processing', function () {
    $order = Order::factory()->vnpay()->create([
        'tracking_code' => 'VNPAYTRACK34',
        'total' => 150000,
    ]);
    $txnRef = $order->id.'_1700000000';
    $order->update(['vnpay_txn_ref' => $txnRef]);

    $params = signedVnpayParams([
        'vnp_TxnRef' => $txnRef,
        'vnp_Amount' => (int) ($order->total * 100),
        'vnp_ResponseCode' => '00',
        'vnp_TransactionStatus' => '00',
    ], 'secret');

    $this->get(route('payment.ipn', $params))
        ->assertOk()
        ->assertJson(['RspCode' => '00']);

    $order->refresh();

    expect($order->payment_status)->toBe('paid')
        ->and($order->status)->toBe('processing')
        ->and($order->statusHistories->last()->payment_status)->toBe('paid');

    Mail::assertQueued(PaymentConfirmed::class, fn ($mail) => $mail->hasTo($order->customer_email));
});

test('ipn is idempotent when order already paid', function () {
    $order = Order::factory()->vnpay()->create([
        'tracking_code' => 'VNPAYTRACK56',
        'payment_status' => 'paid',
        'total' => 150000,
    ]);
    $txnRef = $order->id.'_1700000000';
    $order->update(['vnpay_txn_ref' => $txnRef]);

    $params = signedVnpayParams([
        'vnp_TxnRef' => $txnRef,
        'vnp_Amount' => (int) ($order->total * 100),
        'vnp_ResponseCode' => '00',
        'vnp_TransactionStatus' => '00',
    ], 'secret');

    $this->get(route('payment.ipn', $params))
        ->assertOk()
        ->assertJson(['RspCode' => '02']);

    Mail::assertNothingQueued();
});

test('ipn rejects invalid signature', function () {
    $this->get(route('payment.ipn', [
        'vnp_TxnRef' => '1_1700000000',
        'vnp_Amount' => 15000000,
        'vnp_ResponseCode' => '00',
        'vnp_TransactionStatus' => '00',
        'vnp_SecureHash' => 'bogus',
    ]))
        ->assertOk()
        ->assertJson(['RspCode' => '97']);
});

test('return callback redirects to a public page on invalid signature', function () {
    $this->get(route('payment.callback', ['vnp_SecureHash' => 'bogus']))
        ->assertRedirect(route('shop.index'))
        ->assertSessionHas('error');
});

test('return callback shows success message without requiring authentication', function () {
    $order = Order::factory()->vnpay()->create([
        'tracking_code' => 'VNPAYTRACK78',
        'total' => 150000,
    ]);
    $txnRef = $order->id.'_1700000000';
    $order->update(['vnpay_txn_ref' => $txnRef]);

    $params = signedVnpayParams([
        'vnp_TxnRef' => $txnRef,
        'vnp_Amount' => (int) ($order->total * 100),
        'vnp_ResponseCode' => '00',
        'vnp_TransactionStatus' => '00',
    ], 'secret');

    $this->get(route('payment.callback', $params))
        ->assertRedirect(route('storefront.orders.show', ['order' => $order->tracking_code]))
        ->assertSessionHas('success');

    expect($order->fresh()->payment_status)->toBe('paid');

    Mail::assertQueued(PaymentConfirmed::class, fn ($mail) => $mail->hasTo($order->customer_email));
});
