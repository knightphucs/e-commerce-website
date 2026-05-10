<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreProductRequest;
use App\Http\Requests\UpdateProductRequest;
use App\Models\Category;
use App\Models\Product;
use App\Models\ProductImage;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class ProductController extends Controller
{
    public function index(Request $request): View
    {
        $products = Product::with(['category', 'primaryImage'])
            ->when($request->search, fn ($q) => $q->where('name', 'like', "%{$request->search}%"))
            ->when($request->category_id, fn ($q) => $q->where('category_id', $request->category_id))
            ->latest()
            ->paginate(15)
            ->withQueryString();

        $categories = Category::where('status', 'active')->orderBy('name')->get();

        return view('products.index', compact('products', 'categories'));
    }

    public function create(): View
    {
        $categories = Category::where('status', 'active')->orderBy('name')->get();

        return view('products.create', compact('categories'));
    }

    public function store(StoreProductRequest $request): RedirectResponse
    {
        $product = Product::create($request->safe()->except('images'));

        if ($request->hasFile('images')) {
            foreach ($request->file('images') as $index => $image) {
                $path = $image->store('products', 'public');
                $product->images()->create([
                    'path' => $path,
                    'is_primary' => $index === 0,
                    'sort_order' => $index,
                ]);
            }
        }

        return redirect()->route('products.index')
            ->with('success', 'Sản phẩm đã được tạo thành công.');
    }

    public function edit(Product $product): View
    {
        $categories = Category::where('status', 'active')->orderBy('name')->get();
        $product->load('images');

        return view('products.edit', compact('product', 'categories'));
    }

    public function update(UpdateProductRequest $request, Product $product): RedirectResponse
    {
        $product->update($request->safe()->except('images'));

        if ($request->hasFile('images')) {
            $existingCount = $product->images()->count();
            foreach ($request->file('images') as $index => $image) {
                $path = $image->store('products', 'public');
                $product->images()->create([
                    'path' => $path,
                    'is_primary' => $existingCount === 0 && $index === 0,
                    'sort_order' => $existingCount + $index,
                ]);
            }
        }

        return redirect()->route('products.index')
            ->with('success', 'Sản phẩm đã được cập nhật thành công.');
    }

    public function destroy(Product $product): RedirectResponse
    {
        foreach ($product->images as $image) {
            Storage::disk('public')->delete($image->path);
        }

        $product->delete();

        return redirect()->route('products.index')
            ->with('success', 'Sản phẩm đã được xóa thành công.');
    }

    public function destroyImage(ProductImage $image): JsonResponse
    {
        $product = $image->product;
        Storage::disk('public')->delete($image->path);
        $image->delete();

        if ($image->is_primary) {
            $product->images()->first()?->update(['is_primary' => true]);
        }

        return response()->json(['success' => true]);
    }
}
