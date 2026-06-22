<?php

use App\Models\Category;
use App\Models\Product;
use App\Models\ProductImage;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

beforeEach(function () {
    $this->editor = User::factory()->editor()->create();
    Storage::fake('public');
});

// ------- Index -------

test('editor can list products', function () {
    Product::factory()->count(3)->create();

    $this->actingAs($this->editor)
        ->get(route('products.index'))
        ->assertOk()
        ->assertViewIs('products.index')
        ->assertViewHas('products');
});

test('product index supports search by name', function () {
    Product::factory()->create(['name' => 'iPhone 15 Pro', 'slug' => 'iphone-15-pro']);
    Product::factory()->create(['name' => 'Samsung Galaxy', 'slug' => 'samsung-galaxy']);

    $this->actingAs($this->editor)
        ->get(route('products.index', ['search' => 'iPhone']))
        ->assertOk()
        ->assertSee('iPhone 15 Pro')
        ->assertDontSee('Samsung Galaxy');
});

test('product index supports category filter', function () {
    $catA = Category::factory()->create(['name' => 'Cat A', 'slug' => 'cat-a']);
    $catB = Category::factory()->create(['name' => 'Cat B', 'slug' => 'cat-b']);
    $productA = Product::factory()->create(['category_id' => $catA->id, 'name' => 'Product A', 'slug' => 'product-a']);
    Product::factory()->create(['category_id' => $catB->id, 'name' => 'Product B', 'slug' => 'product-b']);

    $this->actingAs($this->editor)
        ->get(route('products.index', ['category_id' => $catA->id]))
        ->assertOk()
        ->assertSee('Product A')
        ->assertDontSee('Product B');
});

test('product index supports status filter', function () {
    Product::factory()->create(['name' => 'Active Product', 'slug' => 'active-product', 'status' => 'active']);
    Product::factory()->create(['name' => 'Inactive Product', 'slug' => 'inactive-product', 'status' => 'inactive']);

    $this->actingAs($this->editor)
        ->get(route('products.index', ['status' => 'active']))
        ->assertOk()
        ->assertSee('Active Product')
        ->assertDontSee('Inactive Product');
});

test('product index supports stock status filter', function () {
    Product::factory()->create(['name' => 'In Stock Product', 'slug' => 'in-stock-product', 'stock' => 5]);
    Product::factory()->create(['name' => 'Out Of Stock Product', 'slug' => 'out-of-stock-product', 'stock' => 0]);

    $this->actingAs($this->editor)
        ->get(route('products.index', ['stock_status' => 'out_of_stock']))
        ->assertOk()
        ->assertSee('Out Of Stock Product')
        ->assertDontSee('In Stock Product');
});

test('product index supports price range filter', function () {
    Product::factory()->create(['name' => 'Cheap Product', 'slug' => 'cheap-product', 'price' => 100000]);
    Product::factory()->create(['name' => 'Expensive Product', 'slug' => 'expensive-product', 'price' => 900000]);

    $this->actingAs($this->editor)
        ->get(route('products.index', ['price_min' => 500000]))
        ->assertOk()
        ->assertSee('Expensive Product')
        ->assertDontSee('Cheap Product');
});

test('product index supports sorting by price', function () {
    $cheap = Product::factory()->create(['name' => 'Cheap Product', 'slug' => 'cheap-product', 'price' => 100000]);
    $expensive = Product::factory()->create(['name' => 'Expensive Product', 'slug' => 'expensive-product', 'price' => 900000]);

    $response = $this->actingAs($this->editor)
        ->get(route('products.index', ['sort_by' => 'price', 'sort_dir' => 'asc']));

    $response->assertOk();
    $ids = $response->viewData('products')->pluck('id')->all();
    expect($ids)->toBe([$cheap->id, $expensive->id]);
});

// ------- Create -------

test('editor can view create product form', function () {
    $this->actingAs($this->editor)
        ->get(route('products.create'))
        ->assertOk()
        ->assertViewIs('products.create');
});

// ------- Store -------

test('editor can create a product without images', function () {
    $category = Category::factory()->create();

    $this->actingAs($this->editor)
        ->post(route('products.store'), [
            'category_id' => $category->id,
            'name' => 'New Product',
            'slug' => 'new-product',
            'price' => 500000,
            'stock' => 10,
            'status' => 'active',
        ])
        ->assertRedirect(route('products.index'))
        ->assertSessionHas('success');

    $this->assertDatabaseHas('products', ['name' => 'New Product']);
});

test('editor can create a product with images', function () {
    $category = Category::factory()->create();

    $this->actingAs($this->editor)
        ->post(route('products.store'), [
            'category_id' => $category->id,
            'name' => 'Product With Image',
            'slug' => 'product-with-image',
            'price' => 200000,
            'stock' => 5,
            'status' => 'active',
            'images' => [
                UploadedFile::fake()->create('product1.jpg', 100, 'image/jpeg'),
                UploadedFile::fake()->create('product2.jpg', 100, 'image/jpeg'),
            ],
        ])
        ->assertRedirect(route('products.index'));

    $product = Product::where('slug', 'product-with-image')->first();
    expect($product->images()->count())->toBe(2);
    expect($product->images()->where('is_primary', true)->count())->toBe(1);
    expect($product->images()->first()->is_primary)->toBeTrue();
});

test('store requires name and price', function () {
    $this->actingAs($this->editor)
        ->post(route('products.store'), ['name' => '', 'price' => ''])
        ->assertSessionHasErrors(['name', 'price']);
});

// ------- Edit / Update -------

test('editor can view edit product form', function () {
    $product = Product::factory()->create();

    $this->actingAs($this->editor)
        ->get(route('products.edit', $product))
        ->assertOk()
        ->assertViewIs('products.edit')
        ->assertViewHas('product', $product);
});

test('editor can update a product', function () {
    $product = Product::factory()->create(['name' => 'Old Name', 'slug' => 'old-name']);
    $category = Category::factory()->create();

    $this->actingAs($this->editor)
        ->put(route('products.update', $product), [
            'category_id' => $category->id,
            'name' => 'Updated Name',
            'slug' => 'updated-name',
            'price' => 999000,
            'stock' => 20,
            'status' => 'active',
        ])
        ->assertRedirect(route('products.index'))
        ->assertSessionHas('success');

    expect($product->fresh()->name)->toBe('Updated Name');
});

// ------- Destroy -------

test('editor can delete a product and its images are removed from storage', function () {
    $product = Product::factory()->create();
    $image = ProductImage::factory()->create([
        'product_id' => $product->id,
        'path' => 'products/test.jpg',
    ]);
    Storage::disk('public')->put('products/test.jpg', 'fake-content');

    $this->actingAs($this->editor)
        ->delete(route('products.destroy', $product))
        ->assertRedirect(route('products.index'))
        ->assertSessionHas('success');

    $this->assertDatabaseMissing('products', ['id' => $product->id]);
    Storage::disk('public')->assertMissing('products/test.jpg');
});

// ------- Destroy Image -------

test('editor can delete a single product image', function () {
    $product = Product::factory()->create();
    $image = ProductImage::factory()->create([
        'product_id' => $product->id,
        'path' => 'products/img.jpg',
        'is_primary' => true,
    ]);
    Storage::disk('public')->put('products/img.jpg', 'fake-content');

    $this->actingAs($this->editor)
        ->delete(route('products.images.destroy', $image))
        ->assertOk()
        ->assertJson(['success' => true]);

    $this->assertDatabaseMissing('product_images', ['id' => $image->id]);
    Storage::disk('public')->assertMissing('products/img.jpg');
});
