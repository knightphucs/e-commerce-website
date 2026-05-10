<?php

use App\Http\Controllers\CategoryController;
use App\Http\Controllers\ChatbotController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
})->name('home');

Route::middleware(['auth'])->group(function () {
    Route::get('/dashboard', function () {
        return view('dashboard', ['title' => 'Dashboard']);
    })->name('dashboard');

    // User management - Admin only
    Route::resource('users', UserController::class)->middleware('admin');

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
    });

    // Payment
    Route::get('payment/{order}/checkout', [PaymentController::class, 'checkout'])->name('payment.checkout');
});

// VNPay callback (no auth needed - VNPay sends back to this URL)
Route::get('payment/callback', [PaymentController::class, 'callback'])->name('payment.callback');

// Chatbot API - public (customer support)
Route::post('chatbot/message', [ChatbotController::class, 'send'])->name('chatbot.send');

require __DIR__.'/auth.php';
