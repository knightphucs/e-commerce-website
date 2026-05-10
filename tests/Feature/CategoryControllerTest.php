<?php

use App\Models\Category;
use App\Models\Product;
use App\Models\User;

beforeEach(function () {
    $this->editor = User::factory()->editor()->create();
});

// ------- Index -------

test('editor can list categories', function () {
    Category::factory()->count(3)->create();

    $this->actingAs($this->editor)
        ->get(route('categories.index'))
        ->assertOk()
        ->assertViewIs('categories.index')
        ->assertViewHas('categories');
});

test('category index supports search by name', function () {
    Category::factory()->create(['name' => 'Điện thoại', 'slug' => 'dien-thoai']);
    Category::factory()->create(['name' => 'Thời trang', 'slug' => 'thoi-trang']);

    $this->actingAs($this->editor)
        ->get(route('categories.index', ['search' => 'Điện']))
        ->assertOk()
        ->assertSee('Điện thoại')
        ->assertDontSee('Thời trang');
});

// ------- Create -------

test('editor can view create category form', function () {
    $this->actingAs($this->editor)
        ->get(route('categories.create'))
        ->assertOk()
        ->assertViewIs('categories.create');
});

// ------- Store -------

test('editor can create a category', function () {
    $this->actingAs($this->editor)
        ->post(route('categories.store'), [
            'name' => 'Điện tử',
            'slug' => 'dien-tu',
            'description' => 'Danh mục đồ điện tử',
            'status' => 'active',
        ])
        ->assertRedirect(route('categories.index'))
        ->assertSessionHas('success');

    $this->assertDatabaseHas('categories', ['name' => 'Điện tử', 'slug' => 'dien-tu']);
});

test('store requires name', function () {
    $this->actingAs($this->editor)
        ->post(route('categories.store'), ['name' => '', 'slug' => 'test'])
        ->assertSessionHasErrors('name');
});

test('store slug must be unique', function () {
    Category::factory()->create(['name' => 'Existing', 'slug' => 'existing-slug']);

    $this->actingAs($this->editor)
        ->post(route('categories.store'), ['name' => 'New', 'slug' => 'existing-slug', 'status' => 'active'])
        ->assertSessionHasErrors('slug');
});

test('slug is auto generated from name if not provided', function () {
    $this->actingAs($this->editor)
        ->post(route('categories.store'), [
            'name' => 'Auto Slug Test',
            'status' => 'active',
        ])
        ->assertRedirect(route('categories.index'));

    $this->assertDatabaseHas('categories', ['slug' => 'auto-slug-test']);
});

// ------- Edit / Update -------

test('editor can view edit category form', function () {
    $category = Category::factory()->create();

    $this->actingAs($this->editor)
        ->get(route('categories.edit', $category))
        ->assertOk()
        ->assertViewIs('categories.edit')
        ->assertViewHas('category', $category);
});

test('editor can update a category', function () {
    $category = Category::factory()->create(['name' => 'Old Name', 'slug' => 'old-name']);

    $this->actingAs($this->editor)
        ->put(route('categories.update', $category), [
            'name' => 'New Name',
            'slug' => 'new-name',
            'status' => 'active',
        ])
        ->assertRedirect(route('categories.index'))
        ->assertSessionHas('success');

    expect($category->fresh()->name)->toBe('New Name');
});

// ------- Destroy -------

test('editor can delete a category without products', function () {
    $category = Category::factory()->create();

    $this->actingAs($this->editor)
        ->delete(route('categories.destroy', $category))
        ->assertRedirect(route('categories.index'))
        ->assertSessionHas('success');

    $this->assertDatabaseMissing('categories', ['id' => $category->id]);
});

test('editor cannot delete a category that has products', function () {
    $category = Category::factory()->create();
    Product::factory()->create(['category_id' => $category->id]);

    $this->actingAs($this->editor)
        ->delete(route('categories.destroy', $category))
        ->assertRedirect(route('categories.index'))
        ->assertSessionHas('error');

    $this->assertDatabaseHas('categories', ['id' => $category->id]);
});
