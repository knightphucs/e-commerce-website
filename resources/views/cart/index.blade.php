@extends('layouts.storefront')

@section('content')
    <h1 class="mb-6 text-3xl font-semibold">Giỏ hàng</h1>

    @if ($items->isEmpty())
        <div class="rounded-lg border border-gray-200 bg-white p-8 text-center">
            <p class="text-gray-600">Giỏ hàng của bạn đang trống.</p>
            <a href="{{ route('shop.index') }}" class="mt-4 inline-block rounded-lg bg-gray-900 px-4 py-2.5 text-white">Tiếp tục mua sắm</a>
        </div>
    @else
        <div class="grid gap-6 lg:grid-cols-[1fr_320px]">
            <div class="space-y-4">
                @foreach ($items as $item)
                    <article class="flex gap-4 rounded-lg border border-gray-200 bg-white p-4">
                        <div class="h-24 w-24 overflow-hidden rounded-lg bg-gray-100">
                            @if ($item['product']->primaryImage)
                                <img src="{{ $item['product']->primaryImage->url() }}" alt="{{ $item['product']->name }}" class="h-full w-full object-cover">
                            @endif
                        </div>
                        <div class="flex min-w-0 flex-1 flex-col justify-between gap-3">
                            <div>
                                <h2 class="font-medium">{{ $item['product']->name }}</h2>
                                <p class="text-sm text-gray-500">{{ $item['product']->formattedPrice() }}</p>
                            </div>
                            <div class="flex flex-wrap items-center gap-2">
                                <form action="{{ route('cart.update', $item['product']) }}" method="POST" class="flex items-center gap-2">
                                    @csrf
                                    @method('PATCH')
                                    <input name="quantity" type="number" min="1" max="{{ $item['product']->stock }}" value="{{ $item['quantity'] }}"
                                        class="w-20 rounded-lg border border-gray-300 px-3 py-2 text-sm">
                                    <button class="rounded-lg border border-gray-300 px-3 py-2 text-sm">Cập nhật</button>
                                </form>
                                <form action="{{ route('cart.destroy', $item['product']) }}" method="POST">
                                    @csrf
                                    @method('DELETE')
                                    <button class="text-sm text-red-600">Xóa</button>
                                </form>
                            </div>
                        </div>
                        <p class="font-semibold">{{ number_format($item['subtotal'], 0, ',', '.') }} đ</p>
                    </article>
                @endforeach
            </div>
            <aside class="h-fit rounded-lg border border-gray-200 bg-white p-5">
                <div class="flex justify-between text-sm text-gray-600">
                    <span>Tạm tính</span>
                    <span>{{ number_format($total, 0, ',', '.') }} đ</span>
                </div>
                <div class="mt-4 flex justify-between border-t border-gray-200 pt-4 font-semibold">
                    <span>Tổng cộng</span>
                    <span>{{ number_format($total, 0, ',', '.') }} đ</span>
                </div>
                <a href="{{ route('checkout.create') }}" class="mt-5 block rounded-lg bg-gray-900 px-4 py-3 text-center font-medium text-white">Tiến hành thanh toán</a>
            </aside>
        </div>
    @endif
@endsection
