<?php

namespace App\Ai\Tools;

use App\Models\Order;
use App\Models\OrderItem;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Ai\Contracts\Tool;
use Laravel\Ai\Tools\Request;
use Stringable;

class GetOrderDetail implements Tool
{
    public function description(): Stringable|string
    {
        return 'Lấy thông tin chi tiết đầy đủ của một đơn hàng bao gồm danh sách sản phẩm, địa chỉ giao hàng và thanh toán. Nhận mã theo dõi hoặc ID đơn hàng.';
    }

    public function handle(Request $request): Stringable|string
    {
        $identifier = $request->string('identifier')->trim()->toString();

        $order = Order::with(['orderItems.product'])
            ->where('tracking_code', $identifier)
            ->orWhere(function ($q) use ($identifier) {
                if (is_numeric($identifier)) {
                    $q->where('id', (int) $identifier);
                }
            })
            ->first();

        if (! $order) {
            return "Không tìm thấy đơn hàng với mã hoặc ID '{$identifier}'.";
        }

        return json_encode([
            'id' => $order->id,
            'tracking_code' => $order->tracking_code,
            'customer_name' => $order->customer_name,
            'customer_email' => $order->customer_email,
            'customer_phone' => $order->customer_phone,
            'customer_address' => $order->customer_address,
            'status' => $order->statusLabel(),
            'payment_method' => $order->paymentMethodLabel(),
            'payment_status' => $order->paymentStatusLabel(),
            'subtotal' => number_format($order->subtotal, 0, ',', '.').'đ',
            'total' => $order->formattedTotal(),
            'notes' => $order->notes ?: 'Không có ghi chú',
            'created_at' => $order->created_at->format('d/m/Y H:i'),
            'items' => $order->orderItems->map(fn (OrderItem $item) => [
                'product' => $item->product?->name ?? 'Sản phẩm đã bị xóa',
                'quantity' => $item->quantity,
                'unit_price' => number_format($item->unit_price, 0, ',', '.').'đ',
                'subtotal' => $item->formattedSubtotal(),
            ]),
        ], JSON_UNESCAPED_UNICODE);
    }

    public function schema(JsonSchema $schema): array
    {
        return [
            'identifier' => $schema->string()->required()->description('Mã theo dõi (tracking code) hoặc ID số của đơn hàng'),
        ];
    }
}
