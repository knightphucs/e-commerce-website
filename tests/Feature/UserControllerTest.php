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
            'role' => 'editor',
            'status' => 'active',
        ])
        ->assertRedirect(route('users.index'))
        ->assertSessionHas('success');

    expect($user->fresh()->role)->toBe('editor');
});

test('admin cannot change another user\'s name or email', function () {
    $user = User::factory()->create(['name' => 'Original Name', 'email' => 'original@example.com']);

    $this->actingAs($this->admin)
        ->put(route('users.update', $user), [
            'name' => 'Hacked Name',
            'email' => 'hacked@example.com',
            'role' => $user->role,
            'status' => 'active',
        ])
        ->assertRedirect(route('users.index'));

    expect($user->fresh())
        ->name->toBe('Original Name')
        ->email->toBe('original@example.com');
});

test('admin can block another user', function () {
    $user = User::factory()->create();

    $this->actingAs($this->admin)
        ->put(route('users.update', $user), [
            'role' => $user->role,
            'status' => 'blocked',
        ])
        ->assertRedirect(route('users.index'))
        ->assertSessionHas('success');

    expect($user->fresh()->status)->toBe('blocked');
});

test('admin can update their own name and email but not role or status', function () {
    $this->actingAs($this->admin)
        ->put(route('users.update', $this->admin), [
            'name' => 'Updated Admin Name',
            'email' => 'updated-admin@example.com',
        ])
        ->assertRedirect(route('users.index'))
        ->assertSessionHas('success');

    expect($this->admin->fresh())
        ->name->toBe('Updated Admin Name')
        ->email->toBe('updated-admin@example.com')
        ->role->toBe('admin')
        ->status->toBe('active');
});

// ------- Destroy -------

test('admin can delete a user', function () {
    $user = User::factory()->create();

    $this->actingAs($this->admin)
        ->delete(route('users.destroy', $user))
        ->assertRedirect(route('users.index'))
        ->assertSessionHas('success');

    $this->assertDatabaseMissing('users', ['id' => $user->id]);
});

test('admin cannot delete their own account', function () {
    $this->actingAs($this->admin)
        ->delete(route('users.destroy', $this->admin))
        ->assertRedirect(route('users.index'))
        ->assertSessionHas('error');

    $this->assertDatabaseHas('users', ['id' => $this->admin->id]);
});
