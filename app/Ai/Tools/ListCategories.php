<?php

namespace App\Ai\Tools;

use App\Models\Category;
use Illuminate\Contracts\JsonSchema\JsonSchema;
use Laravel\Ai\Contracts\Tool;
use Laravel\Ai\Tools\Request;
use Stringable;

class ListCategories implements Tool
{
    public function description(): Stringable|string
    {
        return 'Liệt kê tất cả danh mục sản phẩm cùng với số lượng sản phẩm trong mỗi danh mục. Dùng khi người dùng muốn xem các danh mục có sẵn.';
    }

    public function handle(Request $request): Stringable|string
    {
        $categories = Category::withCount('products')->get(['id', 'name', 'description']);

        return $categories->toJson();
    }

    public function schema(JsonSchema $schema): array
    {
        return [];
    }
}