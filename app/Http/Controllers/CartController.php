<?php

namespace App\Http\Controllers;

use App\Http\Requests\AddToCartRequest;
use App\Models\Product;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Collection;
use Illuminate\View\View;

class CartController extends Controller
{
    public function index(): View
    {
        return view('cart.index', $this->cartViewData());
    }

    public function store(AddToCartRequest $request, Product $product): RedirectResponse
    {
        abort_unless($product->isActive() && $product->stock > 0, 404);

        $cart = session('cart', []);
        $requestedQuantity = (int) $request->validated('quantity');
        $currentQuantity = (int) ($cart[$product->id] ?? 0);
        $cart[$product->id] = min($currentQuantity + $requestedQuantity, $product->stock);

        session(['cart' => $cart]);

        return redirect()->route('cart.index')->with('success', 'Sản phẩm đã được thêm vào giỏ hàng.');
    }

    public function update(AddToCartRequest $request, Product $product): RedirectResponse
    {
        $cart = session('cart', []);

        if (! isset($cart[$product->id])) {
            return redirect()->route('cart.index');
        }

        $cart[$product->id] = min((int) $request->validated('quantity'), $product->stock);
        session(['cart' => $cart]);

        return redirect()->route('cart.index')->with('success', 'Giỏ hàng đã được cập nhật.');
    }

    public function destroy(Product $product): RedirectResponse
    {
        $cart = session('cart', []);
        unset($cart[$product->id]);
        session(['cart' => $cart]);

        return redirect()->route('cart.index')->with('success', 'Sản phẩm đã được xóa khỏi giỏ hàng.');
    }

    /**
     * @return array{items: Collection<int, array{product: Product, quantity: int, subtotal: float}>, total: float}
     */
    private function cartViewData(): array
    {
        $cart = session('cart', []);

        $products = Product::query()
            ->with('primaryImage')
            ->whereIn('id', array_keys($cart))
            ->get()
            ->keyBy('id');

        $items = collect($cart)
            ->map(function (int $quantity, int|string $productId) use ($products): ?array {
                $product = $products->get((int) $productId);

                if (! $product) {
                    return null;
                }

                $safeQuantity = min($quantity, $product->stock);

                return [
                    'product' => $product,
                    'quantity' => $safeQuantity,
                    'subtotal' => $safeQuantity * (float) $product->price,
                ];
            })
            ->filter()
            ->values();

        return [
            'items' => $items,
            'total' => $items->sum('subtotal'),
        ];
    }
}
