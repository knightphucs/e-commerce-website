<?php

namespace App\Http\Controllers;

use App\Mail\PaymentConfirmed;
use App\Models\Order;
use App\Services\VNPayService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class PaymentController extends Controller
{
    public function __construct(private VNPayService $vnpay) {}

    public function checkout(Order $order): RedirectResponse
    {
        abort_unless(
            $order->payment_method === 'vnpay' && $order->payment_status !== 'paid',
            403,
        );

        $paymentUrl = $this->vnpay->createPaymentUrl($order, $this->getClientIp(request()));

        return redirect()->away($paymentUrl);
    }

    /**
     * Browser redirect after payment. Used only to show the customer a result —
     * VNPay does not guarantee this is called, so it must not be the sole source of truth.
     */
    public function callback(Request $request): RedirectResponse
    {
        $params = $request->all();

        if (! $this->vnpay->verifyCallback($params)) {
            return redirect()->route('shop.index')
                ->with('error', 'Chữ ký thanh toán không hợp lệ.');
        }

        $order = $this->resolveOrder($params);

        if (! $order) {
            return redirect()->route('shop.index')
                ->with('error', 'Không tìm thấy đơn hàng.');
        }

        $this->applyPaymentResult($order, $params, 'return');

        if ($this->vnpay->isPaymentSuccess($params)) {
            return redirect()->route('storefront.orders.show', ['order' => $order->tracking_code])
                ->with('success', 'Thanh toán thành công!');
        }

        return redirect()->route('storefront.orders.show', ['order' => $order->tracking_code])
            ->with('error', 'Thanh toán thất bại. Vui lòng thử lại.');
    }

    /**
     * Server-to-server notification from VNPay. This is the authoritative source for
     * confirming payment, since the browser callback above may never fire.
     */
    public function ipn(Request $request): JsonResponse
    {
        $params = $request->all();

        if (! $this->vnpay->verifyCallback($params)) {
            return response()->json(['RspCode' => '97', 'Message' => 'Invalid signature']);
        }

        $order = $this->resolveOrder($params);

        if (! $order) {
            return response()->json(['RspCode' => '01', 'Message' => 'Order not found']);
        }

        if ((int) ($params['vnp_Amount'] ?? 0) !== (int) ($order->total * 100)) {
            return response()->json(['RspCode' => '04', 'Message' => 'Invalid amount']);
        }

        if ($order->payment_status === 'paid') {
            return response()->json(['RspCode' => '02', 'Message' => 'Order already confirmed']);
        }

        $this->applyPaymentResult($order, $params, 'ipn');

        return response()->json(['RspCode' => '00', 'Message' => 'Confirm Success']);
    }

    private function resolveOrder(array $params): ?Order
    {
        $txnRef = $params['vnp_TxnRef'] ?? '';
        $orderId = explode('_', $txnRef)[0] ?? null;
        $order = Order::find($orderId);

        if (! $order || $order->payment_method !== 'vnpay' || $order->vnpay_txn_ref !== $txnRef) {
            return null;
        }

        return $order;
    }

    private function applyPaymentResult(Order $order, array $params, string $source): void
    {
        if ($order->payment_status === 'paid') {
            return;
        }

        if (! $this->vnpay->isPaymentSuccess($params)) {
            Log::warning('VNPay payment failed', [
                'order_id' => $order->id,
                'source' => $source,
                'response_code' => $params['vnp_ResponseCode'] ?? null,
            ]);

            return;
        }

        $order->update([
            'payment_status' => 'paid',
            'status' => $order->status === 'pending' ? 'processing' : $order->status,
        ]);

        Mail::to($order->customer_email)->queue(new PaymentConfirmed($order));

        Log::info('VNPay payment confirmed', ['order_id' => $order->id, 'source' => $source]);
    }

    private function getClientIp(Request $request): string
    {
        return $request->ip() ?? '127.0.0.1';
    }
}
