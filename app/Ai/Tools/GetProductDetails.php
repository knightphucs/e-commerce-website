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
        return 'Lấy thông tin chi tiết của một sản phẩm dựa trên ID. Trả về tên, giá, số lượng tồn kho, mô tả và danh mục của sản phẩm. Dùng khi người dùng muốn biết thêm về một sản phẩm cụ thể.';
    }

    public function handle(Request $request): Stringable|string
    {
        $product = Product::with('category')->find($request['product_id']);

        if (! $product) {
            return 'Sản phẩm không tồn tại.';
        }

        return json_encode([
            'id' => $product->id,
            'name' => $product->name,
            'price' => $product->price,
            'stock' => $product->stock,
            'content' => $product->content,
            'category' => $product->category?->name,
        ]);
    }

    public function schema(JsonSchema $schema): array
    {
        return [
            'product_id' => $schema->integer('The ID of the product to retrieve.')->required(),
        ];
    }
}