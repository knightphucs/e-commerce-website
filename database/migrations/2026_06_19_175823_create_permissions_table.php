<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('permissions', function (Blueprint $table): void {
            $table->id();
            $table->string('key')->unique();
            $table->string('group');
            $table->string('label');
            $table->timestamps();
        });

        $now = now();

        DB::table('permissions')->insert(collect([
            ['key' => 'products.view', 'group' => 'products', 'label' => 'Xem sản phẩm'],
            ['key' => 'products.manage', 'group' => 'products', 'label' => 'Thêm/sửa sản phẩm'],
            ['key' => 'products.delete', 'group' => 'products', 'label' => 'Xóa sản phẩm'],
            ['key' => 'categories.view', 'group' => 'categories', 'label' => 'Xem danh mục'],
            ['key' => 'categories.manage', 'group' => 'categories', 'label' => 'Thêm/sửa danh mục'],
            ['key' => 'categories.delete', 'group' => 'categories', 'label' => 'Xóa danh mục'],
            ['key' => 'orders.view', 'group' => 'orders', 'label' => 'Xem đơn hàng'],
            ['key' => 'orders.updateStatus', 'group' => 'orders', 'label' => 'Cập nhật trạng thái giao hàng'],
            ['key' => 'orders.updatePayment', 'group' => 'orders', 'label' => 'Cập nhật trạng thái thanh toán'],
        ])->map(fn (array $permission) => [...$permission, 'created_at' => $now, 'updated_at' => $now])->all());
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('permissions');
    }
};
