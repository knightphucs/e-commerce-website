<?php

use App\Http\Controllers\CartController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\ChatbotController;
use App\Http\Controllers\CheckoutController;
use App\Http\Controllers\CustomerChatbotController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\ShopController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;

Route::get('/', fn () => redirect()->route('shop.index'))->name('home');
Route::get('shop', [ShopController::class, 'index'])->name('shop.index');
Route::get('shop/{product:slug}', [ShopController::class, 'show'])->name('shop.show');
Route::get('cart', [CartController::class, 'index'])->name('cart.index');
Route::post('cart/{product}', [CartController::class, 'store'])->name('cart.store');
Route::patch('cart/{product}', [CartController::class, 'update'])->name('cart.update');
Route::delete('cart/{product}', [CartController::class, 'destroy'])->name('cart.destroy');
Route::middleware('auth')->group(function () {
    Route::get('checkout', [CheckoutController::class, 'create'])->name('checkout.create');
    Route::post('checkout', [CheckoutController::class, 'store'])->name('checkout.store');
});
Route::get('track/{order:tracking_code}', [CheckoutController::class, 'show'])->name('storefront.orders.show');
Route::get('payment/{order:tracking_code}/checkout', [PaymentController::class, 'checkout'])->name('payment.checkout');
Route::post('customer-chatbot/message', [CustomerChatbotController::class, 'send'])
    ->middleware('throttle:20,1')
    ->name('customer-chatbot.send');

Route::middleware(['auth'])->group(function () {
    Route::get('/dashboard', function () {
        return view('dashboard', ['title' => 'Dashboard']);
    })->middleware('editor')->name('dashboard');

    // User management - Admin only
    Route::resource('users', UserController::class)
        ->only(['index', 'create', 'store', 'edit', 'update'])
        ->middleware('admin');

    // Category management - Editor+
    Route::resource('categories', CategoryController::class)->middleware('editor');

    // Product management - Editor+
    Route::resource('products', ProductController::class)->middleware('editor');
    Route::delete('products/images/{image}', [ProductController::class, 'destroyImage'])
        ->middleware('editor')
        ->name('products.images.destroy');

    // Order management - Editor+
    Route::middleware('editor')->group(function () {
        Route::get('orders', [OrderController::class, 'index'])->name('orders.index');
        Route::get('orders/{order}', [OrderController::class, 'show'])->name('orders.show');
        Route::patch('orders/{order}/status', [OrderController::class, 'updateStatus'])->name('orders.updateStatus');
        Route::patch('orders/{order}/payment-status', [OrderController::class, 'updatePaymentStatus'])->name('orders.updatePaymentStatus');
        Route::post('chatbot/message', [ChatbotController::class, 'send'])->name('chatbot.send');
    });
});

// VNPay browser return (no auth needed - VNPay redirects the customer here)
Route::get('payment/callback', [PaymentController::class, 'callback'])->name('payment.callback');
// VNPay IPN (no auth needed - VNPay calls this server-to-server; register it in the merchant portal)
Route::get('payment/ipn', [PaymentController::class, 'ipn'])->name('payment.ipn');

require __DIR__.'/auth.php';
