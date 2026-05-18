@extends('layouts.storefront')

@section('content')
    <section class="mb-8 grid gap-4 lg:grid-cols-[1fr_auto] lg:items-end">
        <div>
            <p class="mb-2 text-sm font-medium text-gray-500">Cửa hàng trực tuyến</p>
            <h1 class="text-3xl font-semibold text-gray-900">Sản phẩm nổi bật</h1>
        </div>
        <form method="GET" class="flex flex-col gap-2 sm:flex-row">
            <input name="search" value="{{ request('search') }}" placeholder="Tìm sản phẩm"
                class="rounded-lg border border-gray-300 px-4 py-2.5 text-sm focus:border-gray-900 focus:outline-none">
            <select name="category" class="rounded-lg border border-gray-300 px-4 py-2.5 text-sm focus:border-gray-900 focus:outline-none">
                <option value="">Tất cả danh mục</option>
                @foreach ($categories as $category)
                    <option value="{{ $category->slug }}" @selected(request('category') === $category->slug)>{{ $category->name }}</option>
                @endforeach
            </select>
            <button class="rounded-lg bg-gray-900 px-4 py-2.5 text-sm font-medium text-white">Lọc</button>
        </form>
    </section>

    <div class="grid gap-5 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4">
        @forelse ($products as $product)
            <article class="overflow-hidden rounded-lg border border-gray-200 bg-white">
                <a href="{{ route('shop.show', $product) }}" class="block aspect-[4/3] bg-gray-100">
                    @if ($product->primaryImage)
                        <img src="{{ $product->primaryImage->url() }}" alt="{{ $product->name }}" class="h-full w-full object-cover">
                    @else
                        <div class="flex h-full items-center justify-center text-sm text-gray-400">Chưa có ảnh</div>
                    @endif
                </a>
                <div class="space-y-3 p-4">
                    <div>
                        <p class="text-sm text-gray-500">{{ $product->category->name }}</p>
                        <a href="{{ route('shop.show', $product) }}" class="font-medium text-gray-900 hover:underline">{{ $product->name }}</a>
                    </div>
                    <div class="flex items-center justify-between gap-3">
                        <span class="font-semibold text-gray-900">{{ $product->formattedPrice() }}</span>
                        <form action="{{ route('cart.store', $product) }}" method="POST">
                            @csrf
                            <input type="hidden" name="quantity" value="1">
                            <button class="rounded-lg bg-gray-900 px-3 py-2 text-sm font-medium text-white">Thêm</button>
                        </form>
                    </div>
                </div>
            </article>
        @empty
            <p class="text-gray-500">Không tìm thấy sản phẩm phù hợp.</p>
        @endforelse
    </div>

    <div class="mt-8">{{ $products->links() }}</div>
@endsection
