<?php

use App\Models\User;

// ------- Guest access -------

test('guest is redirected from categories index', function () {
    $this->get(route('categories.index'))->assertRedirect(route('login'));
});

test('guest is redirected from products index', function () {
    $this->get(route('products.index'))->assertRedirect(route('login'));
});

test('guest is redirected from orders index', function () {
    $this->get(route('orders.index'))->assertRedirect(route('login'));
});

test('guest is redirected from dashboard', function () {
    $this->get(route('dashboard'))->assertRedirect(route('login'));
});

// ------- Customer access -------

test('customer cannot access categories', function () {
    $customer = User::factory()->create(['role' => 'customer']);

    $this->actingAs($customer)->get(route('categories.index'))->assertForbidden();
});

test('customer cannot access products', function () {
    $customer = User::factory()->create(['role' => 'customer']);

    $this->actingAs($customer)->get(route('products.index'))->assertForbidden();
});

test('customer cannot access orders', function () {
    $customer = User::factory()->create(['role' => 'customer']);

    $this->actingAs($customer)->get(route('orders.index'))->assertForbidden();
});

test('customer cannot access user management', function () {
    $customer = User::factory()->create(['role' => 'customer']);

    $this->actingAs($customer)->get(route('users.index'))->assertForbidden();
});

test('customer cannot access dashboard', function () {
    $customer = User::factory()->create(['role' => 'customer']);

    $this->actingAs($customer)->get(route('dashboard'))->assertForbidden();
});

test('customer cannot use admin chatbot', function () {
    $customer = User::factory()->create(['role' => 'customer']);

    $this->actingAs($customer)
        ->postJson(route('chatbot.send'), ['message' => 'Xin chào'])
        ->assertForbidden();
});

// ------- Editor access -------

test('editor can access categories', function () {
    $editor = User::factory()->editor()->create();

    $this->actingAs($editor)->get(route('categories.index'))->assertOk();
});

test('editor can access products', function () {
    $editor = User::factory()->editor()->create();

    $this->actingAs($editor)->get(route('products.index'))->assertOk();
});

test('editor can access orders', function () {
    $editor = User::factory()->editor()->create();

    $this->actingAs($editor)->get(route('orders.index'))->assertOk();
});

test('editor cannot access user management', function () {
    $editor = User::factory()->editor()->create();

    $this->actingAs($editor)->get(route('users.index'))->assertForbidden();
});

test('editor can access dashboard without seeing user management menu', function () {
    $editor = User::factory()->editor()->create();

    $this->actingAs($editor)
        ->get(route('dashboard'))
        ->assertOk()
        ->assertDontSee('Quản lý người dùng');
});

// ------- Admin access -------

test('admin can access categories', function () {
    $admin = User::factory()->admin()->create();

    $this->actingAs($admin)->get(route('categories.index'))->assertOk();
});

test('admin can access products', function () {
    $admin = User::factory()->admin()->create();

    $this->actingAs($admin)->get(route('products.index'))->assertOk();
});

test('admin can access orders', function () {
    $admin = User::factory()->admin()->create();

    $this->actingAs($admin)->get(route('orders.index'))->assertOk();
});

test('admin can access user management', function () {
    $admin = User::factory()->admin()->create();

    $this->actingAs($admin)->get(route('users.index'))->assertOk();
});

test('admin can access dashboard and see user management menu', function () {
    $admin = User::factory()->admin()->create();

    $this->actingAs($admin)
        ->get(route('dashboard'))
        ->assertOk()
        ->assertSee('Quản lý người dùng');
});
