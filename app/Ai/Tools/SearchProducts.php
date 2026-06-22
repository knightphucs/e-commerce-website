<?php

namespace App\Ai\Tools;

use App\Models\Product;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Ai\Contracts\Tool;
use Laravel\Ai\Tools\Request;
use Stringable;

class SearchProducts implements Tool
{
    public function description(): Stringable|string
    {
        return 'Tìm kiếm sản phẩm đang bán theo tên hoặc danh mục. Trả về ID, tên, danh mục, giá và tồn kho — dùng ID này với GetProductDetails để xem mô tả đầy đủ.';
    }

    public function handle(Request $request): Stringable|string
    {
        $query = $request->string('query', '')->trim()->toString();
        $category = $request->string('category', '')->trim()->toString();
        $limit = min($request->integer('limit', 10), 20);

        $products = Product::with('category')
            ->where('status', 'active')
            ->when($query, fn ($q) => $q->where('name', 'like', "%{$query}%"))
            ->when($category, fn ($q) => $q->whereHas('category', fn ($c) => $c->where('name', 'like', "%{$category}%")))
            ->orderBy('name')
            ->limit($limit)
            ->get();

        if ($products->isEmpty()) {
            return 'Không tìm thấy sản phẩm nào phù hợp.';
        }

        return $products->map(fn (Product $product) => [
            'id' => $product->id,
            'name' => $product->name,
            'category' => $product->category?->name ?? 'Chưa phân loại',
            'price' => $product->formattedPrice(),
            'stock' => $product->stock > 0 ? "{$product->stock} sản phẩm" : 'Hết hàng',
        ])->toJson(JSON_UNESCAPED_UNICODE);
    }

    public function schema(JsonSchema $schema): array
    {
        return [
            'query' => $schema->string()->description('Tên sản phẩm hoặc từ khóa cần tìm')->nullable(),
            'category' => $schema->string()->description('Tên danh mục sản phẩm cần lọc')->nullable(),
            'limit' => $schema->integer()->min(1)->max(20)->description('Số kết quả tối đa, mặc định 10')->default(10),
        ];
    }
}
