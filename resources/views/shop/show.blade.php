@extends('layouts.storefront')

@section('content')
    <div class="grid gap-8 lg:grid-cols-2">
        <div class="aspect-square overflow-hidden rounded-lg bg-gray-100">
            @if ($product->images->first())
                <img src="{{ $product->images->first()->url() }}" alt="{{ $product->name }}" class="h-full w-full object-cover">
            @else
                <div class="flex h-full items-center justify-center text-gray-400">Chưa có ảnh</div>
            @endif
        </div>
        <div class="space-y-5">
            <div>
                <p class="text-sm text-gray-500">{{ $product->category->name }}</p>
                <h1 class="mt-1 text-3xl font-semibold text-gray-900">{{ $product->name }}</h1>
            </div>
            <p class="text-2xl font-semibold">{{ $product->formattedPrice() }}</p>
            <p class="leading-7 text-gray-600">{{ $product->description ?: 'Thông tin sản phẩm đang được cập nhật.' }}</p>
            <form action="{{ route('cart.store', $product) }}" method="POST" class="flex items-end gap-3">
                @csrf
                <div>
                    <label class="mb-1 block text-sm font-medium text-gray-700">Số lượng</label>
                    <input name="quantity" type="number" min="1" max="{{ $product->stock }}" value="1"
                        class="w-24 rounded-lg border border-gray-300 px-3 py-2.5 focus:border-gray-900 focus:outline-none">
                </div>
                <button class="rounded-lg bg-gray-900 px-5 py-2.5 font-medium text-white">Thêm vào giỏ</button>
            </form>
        </div>
    </div>
@endsection
