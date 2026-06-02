<?php

namespace App\Ai\Tools;

use App\Models\Product;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Ai\Contracts\Tool;
use Laravel\Ai\Tools\Request;
use Stringable;

class GetLowStockProducts implements Tool
{
    public function description(): Stringable|string
    {
        return 'Lấy danh sách sản phẩm có tồn kho thấp hoặc hết hàng để quản trị viên theo dõi và bổ sung hàng kịp thời.';
    }

    public function handle(Request $request): Stringable|string
    {
        $threshold = $request->integer('threshold', 5);

        $products = Product::with('category')
            ->where('status', 'active')
            ->where('stock', '<=', $threshold)
            ->orderBy('stock')
            ->limit(30)
            ->get();

        if ($products->isEmpty()) {
            return "Không có sản phẩm nào có tồn kho dưới {$threshold} đơn vị.";
        }

        return $products->map(fn (Product $product) => [
            'name' => $product->name,
            'category' => $product->category?->name ?? 'Chưa phân loại',
            'stock' => $product->stock === 0 ? 'Hết hàng' : "{$product->stock} còn lại",
            'price' => $product->formattedPrice(),
        ])->toJson(JSON_UNESCAPED_UNICODE);
    }

    public function schema(JsonSchema $schema): array
    {
        return [
            'threshold' => $schema->integer()->min(0)->max(100)->description('Ngưỡng tồn kho cần cảnh báo, mặc định 5')->default(5),
        ];
    }
}
