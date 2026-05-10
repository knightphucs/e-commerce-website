<?php

namespace Database\Seeders;

use App\Models\Category;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class CategorySeeder extends Seeder
{
    public function run(): void
    {
        $categories = [
            ['name' => 'Điện thoại & Máy tính bảng', 'description' => 'Điện thoại thông minh và máy tính bảng'],
            ['name' => 'Máy tính & Laptop', 'description' => 'Laptop, máy tính để bàn và phụ kiện'],
            ['name' => 'Thời trang nam', 'description' => 'Quần áo, giày dép và phụ kiện nam'],
            ['name' => 'Thời trang nữ', 'description' => 'Quần áo, giày dép và phụ kiện nữ'],
            ['name' => 'Nhà cửa & Đời sống', 'description' => 'Đồ gia dụng và trang trí nội thất'],
            ['name' => 'Sức khỏe & Làm đẹp', 'description' => 'Mỹ phẩm và sản phẩm chăm sóc sức khỏe'],
        ];

        foreach ($categories as $data) {
            Category::firstOrCreate(
                ['slug' => Str::slug($data['name'])],
                array_merge($data, ['slug' => Str::slug($data['name']), 'status' => 'active'])
            );
        }

        // Sub-categories
        $electronics = Category::where('slug', 'dien-thoai-may-tinh-bang')->first();
        if ($electronics) {
            $subs = ['iPhone', 'Samsung', 'Xiaomi', 'Oppo'];
            foreach ($subs as $sub) {
                Category::firstOrCreate(
                    ['slug' => Str::slug($sub)],
                    [
                        'name' => $sub,
                        'slug' => Str::slug($sub),
                        'parent_id' => $electronics->id,
                        'status' => 'active',
                    ]
                );
            }
        }
    }
}
