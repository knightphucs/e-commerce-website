<?php

use App\Models\Category;
use App\Models\Order;
use App\Models\Permission;
use App\Models\Product;
use App\Models\User;

beforeEach(function () {
    $this->admin = User::factory()->admin()->create();
});

test('admin can create an editor with a restricted set of permissions', function () {
    $viewOnly = Permission::where('key', 'products.view')->first();

    $this->actingAs($this->admin)
        ->post(route('users.store'), [
            'name' => 'Restricted Editor',
            'email' => 'restricted@example.com',
            'role' => 'editor',
            'password' => 'password',
            'password_confirmation' => 'password',
            'permissions' => [$viewOnly->id],
        ])
        ->assertRedirect(route('users.index'));

    $user = User::where('email', 'restricted@example.com')->firstOrFail();

    expect($user->permissions)->toHaveCount(1)
        ->and($user->hasPermission('products.view'))->toBeTrue()
        ->and($user->hasPermission('products.manage'))->toBeFalse();
});

test('admin can update a user permissions', function () {
    $user = User::factory()->create(['role' => 'editor']);
    $manage = Permission::where('key', 'orders.view')->first();

    $this->actingAs($this->admin)
        ->put(route('users.update', $user), [
            'role' => 'editor',
            'status' => 'active',
            'permissions' => [$manage->id],
        ])
        ->assertRedirect(route('users.index'));

    expect($user->fresh()->permissions->pluck('key')->all())->toBe(['orders.view']);
});

test('editor without products.manage permission cannot create a product', function () {
    $editor = User::factory()->create(['role' => 'editor']);
    $editor->permissions()->sync(Permission::where('key', 'products.view')->pluck('id'));
    $category = Category::factory()->create();

    $this->actingAs($editor)
        ->post(route('products.store'), [
            'category_id' => $category->id,
            'name' => 'New Product',
            'slug' => 'new-product',
            'price' => 500000,
            'stock' => 10,
            'status' => 'active',
        ])
        ->assertForbidden();
});

test('editor without products.delete permission cannot delete a product', function () {
    $editor = User::factory()->create(['role' => 'editor']);
    $editor->permissions()->sync(Permission::whereIn('key', ['products.view', 'products.manage'])->pluck('id'));
    $product = Product::factory()->create();

    $this->actingAs($editor)
        ->delete(route('products.destroy', $product))
        ->assertForbidden();

    $this->assertDatabaseHas('products', ['id' => $product->id]);
});

test('editor with products.view only can still see the product list', function () {
    $editor = User::factory()->create(['role' => 'editor']);
    $editor->permissions()->sync(Permission::where('key', 'products.view')->pluck('id'));
    Product::factory()->create();

    $this->actingAs($editor)
        ->get(route('products.index'))
        ->assertOk();
});

test('editor without orders.updateStatus permission cannot update order status', function () {
    $editor = User::factory()->create(['role' => 'editor']);
    $editor->permissions()->sync(Permission::where('key', 'orders.view')->pluck('id'));
    $order = Order::factory()->pending()->create();

    $this->actingAs($editor)
        ->patch(route('orders.updateStatus', $order), ['status' => 'processing'])
        ->assertForbidden();

    expect($order->fresh()->status)->toBe('pending');
});

test('editor without orders.updatePayment permission cannot update payment status', function () {
    $editor = User::factory()->create(['role' => 'editor']);
    $editor->permissions()->sync(Permission::where('key', 'orders.view')->pluck('id'));
    $order = Order::factory()->create();

    $this->actingAs($editor)
        ->patch(route('orders.updatePaymentStatus', $order), ['payment_status' => 'paid'])
        ->assertForbidden();

    expect($order->fresh()->payment_status)->toBe('unpaid');
});

test('admin bypasses granular permissions entirely', function () {
    $category = Category::factory()->create();

    $this->actingAs($this->admin)
        ->post(route('products.store'), [
            'category_id' => $category->id,
            'name' => 'Admin Product',
            'slug' => 'admin-product',
            'price' => 500000,
            'stock' => 10,
            'status' => 'active',
        ])
        ->assertRedirect(route('products.index'));

    $this->assertDatabaseHas('products', ['name' => 'Admin Product']);
});
