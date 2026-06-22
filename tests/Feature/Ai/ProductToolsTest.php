<?php

use App\Ai\Agents\CustomerChatbotAgent;
use App\Ai\Tools\GetProductDetails;
use App\Ai\Tools\ListCategories;
use App\Ai\Tools\SearchProducts;
use App\Models\Product;
use Laravel\Ai\Tools\Request;

test('customer chatbot agent has product details and category tools available', function () {
    $tools = collect((new CustomerChatbotAgent)->tools());

    expect($tools->contains(fn ($tool) => $tool instanceof GetProductDetails))->toBeTrue()
        ->and($tools->contains(fn ($tool) => $tool instanceof ListCategories))->toBeTrue();
});

test('search products includes the product id so results can be chained into get product details', function () {
    Product::factory()->create(['name' => 'Áo thun nam']);

    $result = json_decode((new SearchProducts)->handle(new Request(['query' => 'Áo thun'])), true);

    expect($result)->toHaveCount(1)
        ->and($result[0])->toHaveKey('id');
});

test('get product details returns the description for an existing product by id', function () {
    $product = Product::factory()->create([
        'name' => 'Áo thun nam',
        'description' => 'Chất liệu cotton thoáng mát',
    ]);

    $result = json_decode((new GetProductDetails)->handle(new Request(['identifier' => (string) $product->id])), true);

    expect($result)
        ->id->toBe($product->id)
        ->name->toBe('Áo thun nam')
        ->description->toBe('Chất liệu cotton thoáng mát');
});

test('get product details also accepts a slug', function () {
    $product = Product::factory()->create(['slug' => 'ao-thun-nam-9999']);

    $result = json_decode((new GetProductDetails)->handle(new Request(['identifier' => 'ao-thun-nam-9999'])), true);

    expect($result['id'])->toBe($product->id);
});

test('get product details reports when nothing matches', function () {
    $result = (new GetProductDetails)->handle(new Request(['identifier' => 'does-not-exist']));

    expect($result)->toContain('Không tìm thấy sản phẩm');
});
