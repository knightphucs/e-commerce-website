<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreCheckoutRequest;
use App\Models\Order;
use App\Models\Product;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class CheckoutController extends Controller
{
    public function create(): View
    {
        $items = $this->cartItems();
        $user = auth()->user();

        if ($items->isEmpty()) {
            return view('checkout.empty');
        }

        return view('checkout.create', [
            'items' => $items,
            'total' => $items->sum('subtotal'),
            'user' => $user,
        ]);
    }

    public function store(StoreCheckoutRequest $request): RedirectResponse
    {
        $cart = session('cart', []);

        if ($cart === []) {
            throw ValidationException::withMessages([
                'cart' => 'Giỏ hàng đang trống.',
            ]);
        }

        $order = DB::transaction(function () use ($request, $cart): Order {
            $products = Product::query()
                ->whereIn('id', array_keys($cart))
                ->lockForUpdate()
                ->get()
                ->keyBy('id');

            $subtotal = 0;

            foreach ($cart as $productId => $quantity) {
                $product = $products->get((int) $productId);

                if (! $product || ! $product->isActive() || $product->stock < $quantity) {
                    throw ValidationException::withMessages([
                        'cart' => 'Một số sản phẩm không còn đủ tồn kho. Vui lòng kiểm tra lại giỏ hàng.',
                    ]);
                }

                $subtotal += $quantity * (float) $product->price;
            }

            $order = Order::create([
                ...$request->validated(),
                'user_id' => $request->user()->id,
                'subtotal' => $subtotal,
                'total' => $subtotal,
                'tracking_code' => Str::upper(Str::random(12)),
            ]);

            foreach ($cart as $productId => $quantity) {
                $product = $products->get((int) $productId);

                $order->items()->create([
                    'product_id' => $product->id,
                    'quantity' => $quantity,
                    'unit_price' => $product->price,
                ]);

                $product->decrement('stock', $quantity);
            }

            return $order;
        });

        session()->forget('cart');

        if ($order->payment_method === 'vnpay') {
            return redirect()->route('payment.checkout', ['order' => $order->tracking_code]);
        }

        return redirect()->route('storefront.orders.show', ['order' => $order->tracking_code])
            ->with('success', 'Đặt hàng thành công.');
    }

    public function show(Order $order): View
    {
        $order->load('items.product');

        return view('checkout.show', compact('order'));
    }

    private function cartItems(): Collection
    {
        $cart = session('cart', []);

        $products = Product::query()
            ->with('primaryImage')
            ->whereIn('id', array_keys($cart))
            ->get()
            ->keyBy('id');

        return collect($cart)
            ->map(function (int $quantity, int|string $productId) use ($products): ?array {
                $product = $products->get((int) $productId);

                if (! $product) {
                    return null;
                }

                return [
                    'product' => $product,
                    'quantity' => $quantity,
                    'subtotal' => $quantity * (float) $product->price,
                ];
            })
            ->filter()
            ->values();
    }
}
