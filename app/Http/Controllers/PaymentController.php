<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Services\VNPayService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class PaymentController extends Controller
{
    public function __construct(private VNPayService $vnpay) {}

    public function checkout(Order $order): RedirectResponse
    {
        $paymentUrl = $this->vnpay->createPaymentUrl($order, $this->getClientIp(request()));

        return redirect()->away($paymentUrl);
    }

    public function callback(Request $request): RedirectResponse
    {
        $params = $request->all();

        if (! $this->vnpay->verifyCallback($params)) {
            return redirect()->route('orders.index')
                ->with('error', 'Chữ ký thanh toán không hợp lệ.');
        }

        $txnRef = $params['vnp_TxnRef'] ?? '';
        $orderId = explode('_', $txnRef)[0] ?? null;
        $order = Order::find($orderId);

        if (! $order) {
            return redirect()->route('orders.index')
                ->with('error', 'Không tìm thấy đơn hàng.');
        }

        if ($this->vnpay->isPaymentSuccess($params)) {
            $order->update(['payment_status' => 'paid', 'payment_method' => 'vnpay']);

            return redirect()->route('orders.show', $order)
                ->with('success', 'Thanh toán thành công!');
        }

        return redirect()->route('orders.show', $order)
            ->with('error', 'Thanh toán thất bại. Vui lòng thử lại.');
    }

    private function getClientIp(Request $request): string
    {
        return $request->ip() ?? '127.0.0.1';
    }
}
