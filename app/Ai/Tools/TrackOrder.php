<?php

namespace App\Ai\Tools;

use App\Models\Order;
use App\Models\OrderItem;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Ai\Contracts\Tool;
use Laravel\Ai\Tools\Request;
use Stringable;

class TrackOrder implements Tool
{
    public function description(): Stringable|string
    {
        return 'Tra cứu trạng thái đơn hàng của khách hàng theo mã theo dõi. Chỉ trả về thông tin công khai, không lộ dữ liệu nhạy cảm.';
    }

    public function handle(Request $request): Stringable|string
    {
        $trackingCode = $request->string('tracking_code')->trim()->toString();

        $order = Order::with(['orderItems.product'])
            ->where('tracking_code', $trackingCode)
            ->first();

        if (! $order) {
            return "Không tìm thấy đơn hàng với mã theo dõi '{$trackingCode}'. Vui lòng kiểm tra lại mã hoặc liên hệ cửa hàng để được hỗ trợ.";
        }

        $estimatedDelivery = match ($order->status) {
            'pending' => 'Đang chờ xác nhận từ cửa hàng',
            'processing' => 'Đang chuẩn bị hàng, dự kiến giao trong 2–5 ngày làm việc',
            'shipped' => 'Đang trên đường giao, dự kiến đến trong 1–2 ngày',
            'delivered' => 'Đã giao hàng thành công',
            'cancelled' => 'Đơn hàng đã bị hủy',
            default => 'Đang cập nhật',
        };

        return json_encode([
            'tracking_code' => $order->tracking_code,
            'status' => $order->statusLabel(),
            'payment_method' => $order->paymentMethodLabel(),
            'payment_status' => $order->paymentStatusLabel(),
            'ordered_at' => $order->created_at->format('d/m/Y'),
            'estimated_delivery' => $estimatedDelivery,
            'items' => $order->orderItems->map(fn (OrderItem $item) => [
                'product' => $item->product?->name ?? 'Sản phẩm',
                'quantity' => $item->quantity,
            ]),
        ], JSON_UNESCAPED_UNICODE);
    }

    public function schema(JsonSchema $schema): array
    {
        return [
            'tracking_code' => $schema->string()->required()->description('Mã theo dõi đơn hàng (tracking code) mà khách hàng cung cấp'),
        ];
    }
}
