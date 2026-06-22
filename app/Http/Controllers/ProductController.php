<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreProductRequest;
use App\Http\Requests\UpdateProductRequest;
use App\Models\Category;
use App\Models\Product;
use App\Models\ProductImage;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\View\View;

class ProductController extends Controller
{
    private const SORTABLE_COLUMNS = ['price', 'stock', 'created_at'];

    public function index(Request $request): View
    {
        $products = $this->filteredProducts($request)
            ->paginate(15)
            ->withQueryString();

        $categories = Category::where('status', 'active')->orderBy('name')->get();

        return view('products.index', compact('products', 'categories'));
    }

    private function filteredProducts(Request $request): Builder
    {
        return Product::with(['category', 'primaryImage'])
            ->when($request->search, fn ($q) => $q->where('name', 'like', "%{$request->search}%"))
            ->when($request->category_id, fn ($q) => $q->where('category_id', $request->category_id))
            ->when($request->status, fn ($q) => $q->where('status', $request->status))
            ->when($request->stock_status, fn ($q) => $this->applyStockFilter($q, $request->stock_status))
            ->when($request->price_min, fn ($q) => $q->where('price', '>=', $request->price_min))
            ->when($request->price_max, fn ($q) => $q->where('price', '<=', $request->price_max))
            ->orderBy(...$this->resolveSort($request));
    }

    private function applyStockFilter(Builder $query, string $stockStatus): Builder
    {
        return match ($stockStatus) {
            'in_stock' => $query->where('stock', '>', 0),
            'out_of_stock' => $query->where('stock', 0),
            default => $query,
        };
    }

    /**
     * @return array{0: string, 1: string}
     */
    private function resolveSort(Request $request): array
    {
        $sortBy = in_array($request->sort_by, self::SORTABLE_COLUMNS) ? $request->sort_by : 'created_at';
        $sortDir = $request->sort_dir === 'asc' ? 'asc' : 'desc';

        return [$sortBy, $sortDir];
    }

    public function create(): View
    {
        $categories = Category::where('status', 'active')->orderBy('name')->get();

        return view('products.create', compact('categories'));
    }

    public function store(StoreProductRequest $request): RedirectResponse
    {
        $product = Product::create($request->safe()->except(['images', 'library_image_ids']));

        $this->attachImages($product, $request, $product->images()->count());

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
        $product->update($request->safe()->except(['images', 'library_image_ids']));

        $this->attachImages($product, $request, $product->images()->count());

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
            $product?->images()->first()?->update(['is_primary' => true]);
        }

        return response()->json(['success' => true]);
    }

    private function attachImages(Product $product, Request $request, int $startIndex): void
    {
        $index = $startIndex;

        if ($request->hasFile('images')) {
            foreach ($request->file('images') as $image) {
                $path = $image->store('products', 'public');
                $product->images()->create([
                    'path' => $path,
                    'is_primary' => $index === 0,
                    'sort_order' => $index,
                ]);
                $index++;
            }
        }

        foreach ($request->input('library_image_ids', []) as $imageId) {
            $source = ProductImage::findOrFail($imageId);

            if ($source->product_id === null) {
                $source->update([
                    'product_id' => $product->id,
                    'is_primary' => $index === 0,
                    'sort_order' => $index,
                ]);
            } else {
                $product->images()->create([
                    'path' => $this->copyLibraryImage($source),
                    'is_primary' => $index === 0,
                    'sort_order' => $index,
                ]);
            }
            $index++;
        }
    }

    private function copyLibraryImage(ProductImage $source): string
    {
        $extension = pathinfo($source->path, PATHINFO_EXTENSION);
        $newPath = 'products/'.Str::uuid().($extension ? ".{$extension}" : '');

        Storage::disk('public')->copy($source->path, $newPath);

        return $newPath;
    }
}
