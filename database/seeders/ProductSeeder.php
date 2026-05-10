<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class ProductSeeder extends Seeder
{
    public function run(): void
    {
        $category = Category::first();
        if (! $category) {
            return;
        }

        $products = [
            ['name' => 'iPhone 15 Pro Max 256GB', 'price' => 34990000, 'stock' => 50],
            ['name' => 'Samsung Galaxy S24 Ultra', 'price' => 29990000, 'stock' => 30],
            ['name' => 'Xiaomi 14 Pro', 'price' => 18990000, 'stock' => 45],
            ['name' => 'Laptop Dell XPS 15', 'price' => 45000000, 'stock' => 20],
            ['name' => 'MacBook Pro M3 14"', 'price' => 55990000, 'stock' => 15],
            ['name' => 'Áo polo nam cao cấp', 'price' => 350000, 'stock' => 200],
            ['name' => 'Váy đầm nữ mùa hè', 'price' => 450000, 'stock' => 150],
            ['name' => 'Nồi cơm điện Sunhouse 1.8L', 'price' => 890000, 'stock' => 80],
            ['name' => 'Kem dưỡng da ban đêm Innisfree', 'price' => 395000, 'stock' => 120],
            ['name' => 'Tai nghe Sony WH-1000XM5', 'price' => 8490000, 'stock' => 35],
        ];

        $categories = Category::all();

        foreach ($products as $index => $data) {
            $cat = $categories[$index % $categories->count()];
            Product::firstOrCreate(
                ['slug' => Str::slug($data['name'])],
                [
                    'category_id' => $cat->id,
                    'name' => $data['name'],
                    'slug' => Str::slug($data['name']),
                    'description' => "Mô tả sản phẩm {$data['name']}. Sản phẩm chất lượng cao, chính hãng.",
                    'price' => $data['price'],
                    'stock' => $data['stock'],
                    'status' => 'active',
                ]
            );
        }
    }
}
