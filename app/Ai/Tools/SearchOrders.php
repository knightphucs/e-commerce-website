<?php

namespace App\Ai\Tools;

use App\Models\Order;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Ai\Contracts\Tool;
use Laravel\Ai\Tools\Request;
use Stringable;

class SearchOrders implements Tool
{
    public function description(): Stringable|string
    {
        return 'Tìm kiếm đơn hàng theo mã theo dõi, tên khách hàng, email, số điện thoại hoặc trạng thái. Dùng khi cần tra cứu đơn hàng.';
    }

    public function handle(Request $request): Stringable|string
    {
        $query = $request->string('query', '')->trim()->toString();
        $status = $request->string('status', '')->toString();
        $limit = min($request->integer('limit', 10), 20);

        $orders = Order::query()
            ->when($query, function ($q) use ($query) {
                $q->where(function ($sub) use ($query) {
                    $sub->where('tracking_code', 'like', "%{$query}%")
                        ->orWhere('customer_name', 'like', "%{$query}%")
                        ->orWhere('customer_email', 'like', "%{$query}%")
                        ->orWhere('customer_phone', 'like', "%{$query}%");
                });
            })
            ->when($status, fn ($q) => $q->where('status', $status))
            ->latest()
            ->limit($limit)
            ->get();

        if ($orders->isEmpty()) {
            return 'Không tìm thấy đơn hàng nào phù hợp.';
        }

        return $orders->map(fn (Order $order) => [
            'id' => $order->id,
            'tracking_code' => $order->tracking_code,
            'customer_name' => $order->customer_name,
            'customer_email' => $order->customer_email,
            'customer_phone' => $order->customer_phone,
            'status' => $order->statusLabel(),
            'payment_method' => $order->paymentMethodLabel(),
            'payment_status' => $order->paymentStatusLabel(),
            'total' => $order->formattedTotal(),
            'created_at' => $order->created_at->format('d/m/Y H:i'),
        ])->toJson(JSON_UNESCAPED_UNICODE);
    }

    public function schema(JsonSchema $schema): array
    {
        return [
            'query' => $schema->string()->description('Mã theo dõi, tên khách hàng, email hoặc số điện thoại cần tìm')->nullable(),
            'status' => $schema->string()->enum(['pending', 'processing', 'shipped', 'delivered', 'cancelled'])->description('Lọc theo trạng thái đơn hàng')->nullable(),
            'limit' => $schema->integer()->min(1)->max(20)->description('Số kết quả tối đa, mặc định 10')->default(10),
        ];
    }
}
