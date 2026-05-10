<?php

use App\Mail\OrderStatusUpdated;
use App\Models\Order;
use App\Models\User;
use Illuminate\Support\Facades\Mail;

beforeEach(function () {
    $this->editor = User::factory()->editor()->create();
    Mail::fake();
});

// ------- Index -------

test('editor can list orders', function () {
    Order::factory()->count(3)->create();

    $this->actingAs($this->editor)
        ->get(route('orders.index'))
        ->assertOk()
        ->assertViewIs('orders.index')
        ->assertViewHas('orders');
});

test('orders index can be filtered by status', function () {
    Order::factory()->pending()->create(['customer_name' => 'Pending Customer']);
    Order::factory()->delivered()->create(['customer_name' => 'Delivered Customer']);

    $this->actingAs($this->editor)
        ->get(route('orders.index', ['status' => 'pending']))
        ->assertOk()
        ->assertSee('Pending Customer')
        ->assertDontSee('Delivered Customer');
});

test('orders index can be filtered by date range', function () {
    Order::factory()->create([
        'customer_name' => 'Old Order',
        'created_at' => now()->subDays(10),
    ]);
    Order::factory()->create([
        'customer_name' => 'Recent Order',
        'created_at' => now()->subDay(),
    ]);

    $this->actingAs($this->editor)
        ->get(route('orders.index', [
            'date_from' => now()->subDays(3)->format('Y-m-d'),
            'date_to' => now()->format('Y-m-d'),
        ]))
        ->assertOk()
        ->assertSee('Recent Order')
        ->assertDontSee('Old Order');
});

// ------- Show -------

test('editor can view order details', function () {
    $order = Order::factory()->create(['customer_name' => 'Test Customer']);

    $this->actingAs($this->editor)
        ->get(route('orders.show', $order))
        ->assertOk()
        ->assertViewIs('orders.show')
        ->assertViewHas('order')
        ->assertSee('Test Customer');
});

// ------- Update Status -------

test('editor can update order status', function () {
    $order = Order::factory()->pending()->create();

    $this->actingAs($this->editor)
        ->patch(route('orders.updateStatus', $order), ['status' => 'processing'])
        ->assertRedirect(route('orders.show', $order))
        ->assertSessionHas('success');

    expect($order->fresh()->status)->toBe('processing');
});

test('email notification is sent when order status is updated', function () {
    $order = Order::factory()->pending()->create();

    $this->actingAs($this->editor)
        ->patch(route('orders.updateStatus', $order), ['status' => 'shipped']);

    Mail::assertQueued(OrderStatusUpdated::class, fn ($mail) => $mail->hasTo($order->customer_email));
});

test('update status requires valid status value', function () {
    $order = Order::factory()->pending()->create();

    $this->actingAs($this->editor)
        ->patch(route('orders.updateStatus', $order), ['status' => 'invalid-status'])
        ->assertSessionHasErrors('status');
});

test('update status does not send email if validation fails', function () {
    $order = Order::factory()->pending()->create();

    $this->actingAs($this->editor)
        ->patch(route('orders.updateStatus', $order), ['status' => 'bad']);

    Mail::assertNothingQueued();
});
