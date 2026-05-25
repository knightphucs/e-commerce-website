<?php

use App\Models\Product;

test('shop page lists active in stock products only', function () {
    $activeProduct = Product::factory()->create(['name' => 'Visible Product']);
    Product::factory()->inactive()->create(['name' => 'Hidden Product']);
    Product::factory()->outOfStock()->create(['name' => 'Sold Out Product']);

    $this->get(route('shop.index'))
        ->assertOk()
        ->assertSee($activeProduct->name)
        ->assertDontSee('Hidden Product')
        ->assertDontSee('Sold Out Product');
});

test('customer can add a product to cart', function () {
    $product = Product::factory()->create(['stock' => 5]);

    $this->post(route('cart.store', $product), ['quantity' => 2])
        ->assertRedirect(route('cart.index'));

    expect(session('cart.'.$product->id))->toBe(2);
});

test('storefront shows the customer chatbot launcher on shop page', function () {
    Product::factory()->create();

    $this->get(route('shop.index'))
        ->assertOk()
        ->assertSee('data-testid="customer-chatbot"', false)
        ->assertSee('fixed right-3 bottom-3', false)
        ->assertSee('style="right: 1.25rem; bottom: 1.25rem; left: auto;"', false)
        ->assertSee('data-testid="customer-chatbot-launcher"', false)
        ->assertSee('aria-label="Mở chatbot hỗ trợ"', false)
        ->assertSee('data-testid="customer-chatbot-window"', false)
        ->assertSee('style="display: none;"', false)
        ->assertSee('Nhập câu hỏi...')
        ->assertSee('aria-label="Gửi tin nhắn"', false);
});
