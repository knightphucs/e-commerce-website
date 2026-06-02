<?php

namespace App\Ai\Tools;

use App\Models\Order;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Ai\Contracts\Tool;
use Laravel\Ai\Tools\Request;
use Stringable;

class GetSalesStats implements Tool
{
    public function description(): Stringable|string
    {
        return 'Lấy thống kê doanh thu và số lượng đơn hàng theo khoảng thời gian (hôm nay, tuần này, tháng này).';
    }

    public function handle(Request $request): Stringable|string
    {
        $period = $request->string('period', 'today')->toString();

        $orders = Order::query()
            ->when($period === 'today', fn ($q) => $q->whereDate('created_at', today()))
            ->when($period === 'week', fn ($q) => $q->whereBetween('created_at', [now()->startOfWeek(), now()->endOfWeek()]))
            ->when($period === 'month', fn ($q) => $q->whereMonth('created_at', now()->month)->whereYear('created_at', now()->year))
            ->get();

        $periodLabel = match ($period) {
            'today' => 'Hôm nay ('.today()->format('d/m/Y').')',
            'week' => 'Tuần này',
            'month' => 'Tháng '.now()->format('m/Y'),
            default => 'Hôm nay',
        };

        return json_encode([
            'period' => $periodLabel,
            'total_orders' => $orders->count(),
            'confirmed_revenue' => number_format($orders->where('payment_status', 'paid')->sum('total'), 0, ',', '.').'đ',
            'pending_revenue' => number_format($orders->where('payment_status', 'unpaid')->sum('total'), 0, ',', '.').'đ',
            'orders_by_status' => [
                'Chờ xử lý' => $orders->where('status', 'pending')->count(),
                'Đang xử lý' => $orders->where('status', 'processing')->count(),
                'Đang giao' => $orders->where('status', 'shipped')->count(),
                'Đã giao' => $orders->where('status', 'delivered')->count(),
                'Đã hủy' => $orders->where('status', 'cancelled')->count(),
            ],
            'by_payment_method' => [
                'COD' => $orders->where('payment_method', 'cod')->count(),
                'VNPay' => $orders->where('payment_method', 'vnpay')->count(),
            ],
        ], JSON_UNESCAPED_UNICODE);
    }

    public function schema(JsonSchema $schema): array
    {
        return [
            'period' => $schema->string()->enum(['today', 'week', 'month'])->description('Khoảng thời gian thống kê: today (hôm nay), week (tuần này), month (tháng này)')->default('today'),
        ];
    }
}
