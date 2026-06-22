@extends('layouts.app')

@section('content')
    <x-common.page-breadcrumb pageTitle="Sản phẩm" />

    <div class="space-y-6">
        @session('success')
            <x-ui.alert variant="success">{{ $value }}</x-ui.alert>
        @endsession
        @session('error')
            <x-ui.alert variant="danger">{{ $value }}</x-ui.alert>
        @endsession

        {{-- Toolbar --}}
        <div class="flex justify-end">
            @can('products.manage')
                <a href="{{ route('products.create') }}"
                    class="inline-flex items-center gap-2 rounded-lg bg-brand-500 px-4 py-2 text-sm font-medium text-white hover:bg-brand-600">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none"
                        stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                        <line x1="12" y1="5" x2="12" y2="19" />
                        <line x1="5" y1="12" x2="19" y2="12" />
                    </svg>
                    Thêm sản phẩm
                </a>
            @endcan
        </div>

        {{-- Filters --}}
        <div class="rounded-xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-white/[0.03]">
            <form method="GET" class="flex flex-wrap items-end gap-4">
                <div>
                    <label class="mb-1 block text-xs font-medium text-gray-500 dark:text-gray-400">Tìm kiếm</label>
                    <input type="text" name="search" value="{{ request('search') }}" placeholder="Tên sản phẩm..."
                        class="w-52 rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm focus:border-brand-500 focus:outline-none dark:border-gray-700 dark:bg-gray-800 dark:text-white" />
                </div>
                <div>
                    <label class="mb-1 block text-xs font-medium text-gray-500 dark:text-gray-400">Danh mục</label>
                    <select name="category_id"
                        class="rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm focus:border-brand-500 focus:outline-none dark:border-gray-700 dark:bg-gray-800 dark:text-white">
                        <option value="">Tất cả</option>
                        @foreach ($categories as $cat)
                            <option value="{{ $cat->id }}" @selected(request('category_id') == $cat->id)>{{ $cat->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="mb-1 block text-xs font-medium text-gray-500 dark:text-gray-400">Trạng thái</label>
                    <select name="status"
                        class="rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm focus:border-brand-500 focus:outline-none dark:border-gray-700 dark:bg-gray-800 dark:text-white">
                        <option value="">Tất cả</option>
                        <option value="active" @selected(request('status') === 'active')>Đang bán</option>
                        <option value="inactive" @selected(request('status') === 'inactive')>Ẩn</option>
                    </select>
                </div>
                <div>
                    <label class="mb-1 block text-xs font-medium text-gray-500 dark:text-gray-400">Tồn kho</label>
                    <select name="stock_status"
                        class="rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm focus:border-brand-500 focus:outline-none dark:border-gray-700 dark:bg-gray-800 dark:text-white">
                        <option value="">Tất cả</option>
                        <option value="in_stock" @selected(request('stock_status') === 'in_stock')>Còn hàng</option>
                        <option value="out_of_stock" @selected(request('stock_status') === 'out_of_stock')>Hết hàng</option>
                    </select>
                </div>
                <div>
                    <label class="mb-1 block text-xs font-medium text-gray-500 dark:text-gray-400">Giá từ</label>
                    <input type="number" name="price_min" value="{{ request('price_min') }}" min="0"
                        class="w-28 rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm focus:border-brand-500 focus:outline-none dark:border-gray-700 dark:bg-gray-800 dark:text-white" />
                </div>
                <div>
                    <label class="mb-1 block text-xs font-medium text-gray-500 dark:text-gray-400">Giá đến</label>
                    <input type="number" name="price_max" value="{{ request('price_max') }}" min="0"
                        class="w-28 rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm focus:border-brand-500 focus:outline-none dark:border-gray-700 dark:bg-gray-800 dark:text-white" />
                </div>
                <div>
                    <label class="mb-1 block text-xs font-medium text-gray-500 dark:text-gray-400">Sắp xếp theo</label>
                    <select name="sort_by"
                        class="rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm focus:border-brand-500 focus:outline-none dark:border-gray-700 dark:bg-gray-800 dark:text-white">
                        <option value="created_at" @selected(request('sort_by', 'created_at') === 'created_at')>Ngày tạo</option>
                        <option value="price" @selected(request('sort_by') === 'price')>Giá</option>
                        <option value="stock" @selected(request('sort_by') === 'stock')>Tồn kho</option>
                    </select>
                </div>
                <div>
                    <label class="mb-1 block text-xs font-medium text-gray-500 dark:text-gray-400">Thứ tự</label>
                    <select name="sort_dir"
                        class="rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm focus:border-brand-500 focus:outline-none dark:border-gray-700 dark:bg-gray-800 dark:text-white">
                        <option value="desc" @selected(request('sort_dir', 'desc') === 'desc')>Giảm dần</option>
                        <option value="asc" @selected(request('sort_dir') === 'asc')>Tăng dần</option>
                    </select>
                </div>
                <div class="flex gap-2">
                    <button type="submit"
                        class="rounded-lg bg-brand-500 px-4 py-2 text-sm font-medium text-white hover:bg-brand-600">
                        Lọc
                    </button>
                    @if (request()->hasAny(['search', 'category_id', 'status', 'stock_status', 'price_min', 'price_max', 'sort_by', 'sort_dir']))
                        <a href="{{ route('products.index') }}"
                            class="rounded-lg border border-gray-300 px-4 py-2 text-sm text-gray-600 hover:bg-gray-50 dark:border-gray-700 dark:text-gray-400">
                            Xóa lọc
                        </a>
                    @endif
                </div>
            </form>
        </div>

        {{-- Table --}}
        <div class="overflow-hidden rounded-xl border border-gray-200 bg-white dark:border-gray-800 dark:bg-white/[0.03]">
            <div class="max-w-full overflow-x-auto">
                <table class="w-full min-w-[800px]">
                    <thead>
                        <tr class="border-b border-gray-100 dark:border-gray-800">
                            <th class="px-5 py-3 text-left">
                                <p class="font-medium text-gray-500 text-theme-xs dark:text-gray-400">Hình ảnh</p>
                            </th>
                            <th class="px-5 py-3 text-left">
                                <p class="font-medium text-gray-500 text-theme-xs dark:text-gray-400">Tên sản phẩm</p>
                            </th>
                            <th class="px-5 py-3 text-left">
                                <p class="font-medium text-gray-500 text-theme-xs dark:text-gray-400">Danh mục</p>
                            </th>
                            <th class="px-5 py-3 text-right">
                                <p class="font-medium text-gray-500 text-theme-xs dark:text-gray-400">Giá</p>
                            </th>
                            <th class="px-5 py-3 text-right">
                                <p class="font-medium text-gray-500 text-theme-xs dark:text-gray-400">Tồn kho</p>
                            </th>
                            <th class="px-5 py-3 text-left">
                                <p class="font-medium text-gray-500 text-theme-xs dark:text-gray-400">Trạng thái</p>
                            </th>
                            <th class="px-5 py-3 text-left">
                                <p class="font-medium text-gray-500 text-theme-xs dark:text-gray-400">Thao tác</p>
                            </th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($products as $product)
                            <tr class="border-b border-gray-100 dark:border-gray-800">
                                <td class="px-5 py-4">
                                    @if ($product->primaryImage)
                                        <img src="{{ $product->primaryImage->url() }}" alt="{{ $product->name }}"
                                            class="h-12 w-12 rounded-lg object-cover" />
                                    @else
                                        <div class="flex h-12 w-12 items-center justify-center rounded-lg bg-gray-100 dark:bg-gray-700">
                                            <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20"
                                                viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                                                class="text-gray-400">
                                                <rect x="3" y="3" width="18" height="18" rx="2" ry="2" />
                                                <circle cx="8.5" cy="8.5" r="1.5" />
                                                <polyline points="21 15 16 10 5 21" />
                                            </svg>
                                        </div>
                                    @endif
                                </td>
                                <td class="px-5 py-4">
                                    <p class="font-medium text-gray-800 text-theme-sm dark:text-white/90">{{ $product->name }}</p>
                                    <p class="text-gray-400 text-theme-xs">{{ $product->slug }}</p>
                                </td>
                                <td class="px-5 py-4">
                                    <p class="text-gray-500 text-theme-sm dark:text-gray-400">{{ $product->category->name }}</p>
                                </td>
                                <td class="px-5 py-4 text-right">
                                    <p class="font-medium text-gray-800 text-theme-sm dark:text-white/90">{{ $product->formattedPrice() }}</p>
                                </td>
                                <td class="px-5 py-4 text-right">
                                    <p class="text-gray-500 text-theme-sm dark:text-gray-400">{{ $product->stock }}</p>
                                </td>
                                <td class="px-5 py-4">
                                    @if ($product->status === 'active')
                                        <span class="inline-flex items-center rounded-full bg-success-50 px-2.5 py-0.5 text-xs font-medium text-success-700 dark:bg-success-500/15 dark:text-success-400">
                                            Đang bán
                                        </span>
                                    @else
                                        <span class="inline-flex items-center rounded-full bg-gray-100 px-2.5 py-0.5 text-xs font-medium text-gray-600 dark:bg-gray-800 dark:text-gray-400">
                                            Ẩn
                                        </span>
                                    @endif
                                </td>
                                <td class="px-5 py-4">
                                    <div class="flex items-center gap-2">
                                        @can('products.manage')
                                            <a href="{{ route('products.edit', $product) }}"
                                                class="inline-flex items-center rounded-lg border border-gray-300 bg-white px-3 py-1.5 text-sm font-medium text-gray-700 hover:bg-gray-50 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-400">
                                                Sửa
                                            </a>
                                        @endcan
                                        @can('products.delete')
                                            <form action="{{ route('products.destroy', $product) }}" method="POST"
                                                onsubmit="return confirm('Bạn có chắc muốn xóa sản phẩm này?')">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit"
                                                    class="inline-flex items-center rounded-lg border border-red-300 bg-white px-3 py-1.5 text-sm font-medium text-red-600 hover:bg-red-50 dark:border-red-700 dark:bg-transparent dark:text-red-400">
                                                    Xóa
                                                </button>
                                            </form>
                                        @endcan
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="px-5 py-10 text-center">
                                    <p class="text-gray-500 dark:text-gray-400">Chưa có sản phẩm nào.</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        @if ($products->hasPages())
            <div class="mt-4">
                {{ $products->links() }}
            </div>
        @endif
    </div>
@endsection
