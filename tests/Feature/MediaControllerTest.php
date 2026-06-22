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

test('editor can view media library index', function () {
    $product = Product::factory()->create(['name' => 'Awesome Product']);
    ProductImage::factory()->create([
        'product_id' => $product->id,
        'path' => 'products/test.jpg',
    ]);
    Storage::disk('public')->put('products/test.jpg', 'fake-content');

    $this->actingAs($this->editor)
        ->get(route('media.index'))
        ->assertOk()
        ->assertViewIs('media.index')
        ->assertSee('Awesome Product');
});

test('media index supports search by product name', function () {
    $productA = Product::factory()->create(['name' => 'iPhone 15 Pro']);
    $productB = Product::factory()->create(['name' => 'Samsung Galaxy']);
    ProductImage::factory()->create(['product_id' => $productA->id, 'path' => 'products/a.jpg']);
    ProductImage::factory()->create(['product_id' => $productB->id, 'path' => 'products/b.jpg']);

    $response = $this->actingAs($this->editor)
        ->get(route('media.index', ['search' => 'iPhone']))
        ->assertOk();

    $images = $response->viewData('images');
    expect($images->pluck('product.name')->all())->toBe(['iPhone 15 Pro']);
});

// ------- Picker -------

test('picker endpoint returns images as json', function () {
    $product = Product::factory()->create(['name' => 'Picker Product']);
    ProductImage::factory()->create(['product_id' => $product->id, 'path' => 'products/picker.jpg']);

    $this->actingAs($this->editor)
        ->getJson(route('media.picker'))
        ->assertOk()
        ->assertJsonFragment(['product_name' => 'Picker Product']);
});

// ------- Direct upload from the library page -------

test('editor can upload images directly to a product from the media library', function () {
    $product = Product::factory()->create();

    $this->actingAs($this->editor)
        ->post(route('media.store'), [
            'product_id' => $product->id,
            'images' => [
                UploadedFile::fake()->create('library1.jpg', 100, 'image/jpeg'),
            ],
        ])
        ->assertRedirect(route('media.index'))
        ->assertSessionHas('success');

    expect($product->images()->count())->toBe(1);
    expect($product->images()->first()->is_primary)->toBeTrue();
});

test('media upload requires at least one image', function () {
    $this->actingAs($this->editor)
        ->post(route('media.store'), [])
        ->assertSessionHasErrors(['images']);
});

test('editor can upload images to the library without assigning a product', function () {
    $this->actingAs($this->editor)
        ->post(route('media.store'), [
            'images' => [
                UploadedFile::fake()->create('unassigned.jpg', 100, 'image/jpeg'),
            ],
        ])
        ->assertRedirect(route('media.index'))
        ->assertSessionHas('success');

    $image = ProductImage::first();
    expect($image->product_id)->toBeNull();
    expect($image->is_primary)->toBeFalse();
});

// ------- Assign -------

test('editor can assign an unassigned image to a product', function () {
    $image = ProductImage::factory()->create(['product_id' => null]);
    $product = Product::factory()->create();

    $this->actingAs($this->editor)
        ->post(route('media.assign', $image), ['product_id' => $product->id])
        ->assertRedirect(route('media.index'))
        ->assertSessionHas('success');

    $image->refresh();
    expect($image->product_id)->toBe($product->id);
    expect($image->is_primary)->toBeTrue();
    expect($image->sort_order)->toBe(0);
});

test('assigning an already-assigned image is rejected', function () {
    $owner = Product::factory()->create();
    $image = ProductImage::factory()->create(['product_id' => $owner->id]);
    $otherProduct = Product::factory()->create();

    $this->actingAs($this->editor)
        ->post(route('media.assign', $image), ['product_id' => $otherProduct->id])
        ->assertNotFound();

    expect($image->fresh()->product_id)->toBe($owner->id);
});

// ------- Attach from library on store/update -------

test('editor can pick an existing image from the library when creating a product', function () {
    $sourceProduct = Product::factory()->create();
    $sourceImage = ProductImage::factory()->create([
        'product_id' => $sourceProduct->id,
        'path' => 'products/source.jpg',
    ]);
    Storage::disk('public')->put('products/source.jpg', 'fake-content');

    $category = Category::factory()->create();

    $this->actingAs($this->editor)
        ->post(route('products.store'), [
            'category_id' => $category->id,
            'name' => 'Product From Library',
            'slug' => 'product-from-library',
            'price' => 300000,
            'stock' => 5,
            'status' => 'active',
            'library_image_ids' => [$sourceImage->id],
        ])
        ->assertRedirect(route('products.index'));

    $newProduct = Product::where('slug', 'product-from-library')->first();
    expect($newProduct->images()->count())->toBe(1);

    $newImage = $newProduct->images()->first();
    expect($newImage->path)->not->toBe($sourceImage->path);
    expect($newImage->is_primary)->toBeTrue();

    Storage::disk('public')->assertExists($sourceImage->path);
    Storage::disk('public')->assertExists($newImage->path);

    // Original image/product must be untouched.
    expect($sourceImage->fresh()->product_id)->toBe($sourceProduct->id);
});

test('editor can pick an unassigned library image when creating a product', function () {
    $unassignedImage = ProductImage::factory()->create([
        'product_id' => null,
        'path' => 'products/unassigned.jpg',
    ]);
    Storage::disk('public')->put('products/unassigned.jpg', 'fake-content');

    $category = Category::factory()->create();

    $this->actingAs($this->editor)
        ->post(route('products.store'), [
            'category_id' => $category->id,
            'name' => 'Product From Unassigned',
            'slug' => 'product-from-unassigned',
            'price' => 150000,
            'stock' => 3,
            'status' => 'active',
            'library_image_ids' => [$unassignedImage->id],
        ])
        ->assertRedirect(route('products.index'));

    $newProduct = Product::where('slug', 'product-from-unassigned')->first();
    expect($newProduct->images()->count())->toBe(1);

    // Moved, not copied: same row, same path, now owned by the new product.
    $unassignedImage->refresh();
    expect($unassignedImage->product_id)->toBe($newProduct->id);
    expect($unassignedImage->path)->toBe('products/unassigned.jpg');
    expect($unassignedImage->is_primary)->toBeTrue();

    expect(ProductImage::count())->toBe(1);
});

test('editor can add a library image when updating a product that already has images', function () {
    $sourceProduct = Product::factory()->create();
    $sourceImage = ProductImage::factory()->create([
        'product_id' => $sourceProduct->id,
        'path' => 'products/source.jpg',
    ]);
    Storage::disk('public')->put('products/source.jpg', 'fake-content');

    $product = Product::factory()->create();
    ProductImage::factory()->create([
        'product_id' => $product->id,
        'path' => 'products/existing.jpg',
        'is_primary' => true,
        'sort_order' => 0,
    ]);
    $category = Category::factory()->create();

    $this->actingAs($this->editor)
        ->put(route('products.update', $product), [
            'category_id' => $category->id,
            'name' => $product->name,
            'slug' => $product->slug,
            'price' => $product->price,
            'stock' => $product->stock,
            'status' => $product->status,
            'library_image_ids' => [$sourceImage->id],
        ])
        ->assertRedirect(route('products.index'));

    expect($product->images()->count())->toBe(2);
    $newImage = ProductImage::where('product_id', $product->id)->orderByDesc('sort_order')->first();
    expect($newImage->is_primary)->toBeFalse();
    expect($newImage->sort_order)->toBe(1);
});
