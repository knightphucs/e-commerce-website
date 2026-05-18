<?php

use App\Models\User;

beforeEach(function () {
    $this->admin = User::factory()->admin()->create();
});

test('admin can view create user form', function () {
    $this->actingAs($this->admin)
        ->get(route('users.create'))
        ->assertOk()
        ->assertViewIs('users.create');
});

test('admin can create a user', function () {
    $this->actingAs($this->admin)
        ->post(route('users.store'), [
            'name' => 'Operations Editor',
            'email' => 'editor@example.com',
            'role' => 'editor',
            'password' => 'password',
            'password_confirmation' => 'password',
        ])
        ->assertRedirect(route('users.index'))
        ->assertSessionHas('success');

    $user = User::where('email', 'editor@example.com')->firstOrFail();

    expect($user->name)->toBe('Operations Editor')
        ->and($user->role)->toBe('editor');
});

test('admin can update a user role', function () {
    $user = User::factory()->create(['role' => 'customer']);

    $this->actingAs($this->admin)
        ->put(route('users.update', $user), [
            'name' => $user->name,
            'email' => $user->email,
            'role' => 'editor',
        ])
        ->assertRedirect(route('users.index'))
        ->assertSessionHas('success');

    expect($user->fresh()->role)->toBe('editor');
});
