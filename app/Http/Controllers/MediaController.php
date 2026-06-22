<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreMediaRequest;
use App\Models\Product;
use App\Models\ProductImage;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class MediaController extends Controller
{
    public function index(Request $request): View
    {
        $images = ProductImage::with('product')
            ->when($request->search, fn ($q) => $q->whereHas('product', fn ($q) => $q->where('name', 'like', "%{$request->search}%")))
            ->latest()
            ->paginate(24)
            ->withQueryString();

        $products = Product::orderBy('name')->get(['id', 'name']);

        return view('media.index', compact('images', 'products'));
    }

    public function store(StoreMediaRequest $request): RedirectResponse
    {
        $productId = $request->validated('product_id');
        $product = $productId ? Product::findOrFail($productId) : null;
        $index = $product?->images()->count() ?? 0;

        foreach ($request->file('images') as $image) {
            $path = $image->store('products', 'public');

            if ($product) {
                $product->images()->create([
                    'path' => $path,
                    'is_primary' => $index === 0,
                    'sort_order' => $index,
                ]);
            } else {
                ProductImage::create([
                    'product_id' => null,
                    'path' => $path,
                    'is_primary' => false,
                    'sort_order' => 0,
                ]);
            }
            $index++;
        }

        return redirect()->route('media.index')
            ->with('success', 'Ảnh đã được thêm vào thư viện.');
    }

    public function assign(Request $request, ProductImage $image): RedirectResponse
    {
        abort_if($image->product_id !== null, 404);

        $data = $request->validate([
            'product_id' => ['required', 'integer', 'exists:products,id'],
        ]);

        $product = Product::findOrFail($data['product_id']);
        $existingCount = $product->images()->count();

        $image->update([
            'product_id' => $product->id,
            'is_primary' => $existingCount === 0,
            'sort_order' => $existingCount,
        ]);

        return redirect()->route('media.index')
            ->with('success', 'Ảnh đã được gán cho sản phẩm.');
    }

    public function picker(Request $request): JsonResponse
    {
        $images = ProductImage::with('product')
            ->when($request->search, fn ($q) => $q->whereHas('product', fn ($q) => $q->where('name', 'like', "%{$request->search}%")))
            ->latest()
            ->paginate(24);

        return response()->json([
            'data' => $images->getCollection()->map(fn (ProductImage $image) => [
                'id' => $image->id,
                'url' => $image->url(),
                'product_name' => optional($image->product)->name,
            ]),
            'next_page_url' => $images->nextPageUrl(),
        ]);
    }
}
