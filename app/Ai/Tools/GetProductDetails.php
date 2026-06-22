<?php

namespace App\Ai\Tools;

use App\Models\Product;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Ai\Contracts\Tool;
use Laravel\Ai\Tools\Request;
use Stringable;

class GetProductDetails implements Tool
{
    public function description(): Stringable|string
    {
        return 'Lấy thông tin chi tiết đầy đủ của một sản phẩm, bao gồm mô tả. Nhận ID (lấy từ kết quả SearchProducts) hoặc slug của sản phẩm.';
    }

    public function handle(Request $request): Stringable|string
    {
        $identifier = $request->string('identifier')->trim()->toString();

        $product = Product::with('category')
            ->where('slug', $identifier)
            ->when(is_numeric($identifier), fn ($q) => $q->orWhere('id', (int) $identifier))
            ->first();

        if (! $product) {
            return "Không tìm thấy sản phẩm với ID hoặc slug '{$identifier}'.";
        }

        return json_encode([
            'id' => $product->id,
            'name' => $product->name,
            'category' => $product->category?->name ?? 'Chưa phân loại',
            'price' => $product->formattedPrice(),
            'stock' => $product->stock > 0 ? "{$product->stock} sản phẩm" : 'Hết hàng',
            'description' => $product->description ?: 'Không có mô tả',
        ], JSON_UNESCAPED_UNICODE);
    }

    public function schema(JsonSchema $schema): array
    {
        return [
            'identifier' => $schema->string()->required()->description('ID hoặc slug của sản phẩm cần xem chi tiết'),
        ];
    }
}
