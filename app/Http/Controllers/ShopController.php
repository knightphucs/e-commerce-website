<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ShopController extends Controller
{
    public function index(Request $request): View
    {
        $products = Product::query()
            ->with(['category', 'primaryImage'])
            ->where('status', 'active')
            ->where('stock', '>', 0)
            ->when($request->category, fn ($query) => $query->whereHas('category', fn ($categoryQuery) => $categoryQuery->where('slug', $request->category)))
            ->when($request->search, fn ($query) => $query->where('name', 'like', '%'.$request->search.'%'))
            ->latest()
            ->paginate(12)
            ->withQueryString();

        $categories = Category::query()
            ->where('status', 'active')
            ->orderBy('name')
            ->get();

        return view('shop.index', compact('products', 'categories'));
    }

    public function show(Product $product): View
    {
        abort_unless($product->isActive(), 404);

        $product->load(['category', 'images']);

        return view('shop.show', compact('product'));
    }
}
