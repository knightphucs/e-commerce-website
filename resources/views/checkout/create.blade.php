@extends('layouts.storefront')

@section('content')
    <div class="grid gap-6 lg:grid-cols-[1fr_360px]">
        <section class="rounded-lg border border-gray-200 bg-white p-6">
            <h1 class="mb-6 text-2xl font-semibold">Thông tin đặt hàng</h1>
            @error('cart')
                <div class="mb-4 rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-700">{{ $message }}</div>
            @enderror
            <form action="{{ route('checkout.store') }}" method="POST" class="space-y-4">
                @csrf
                <div class="grid gap-4 sm:grid-cols-2">
                    <div>
                        <label class="mb-1 block text-sm font-medium">Họ tên</label>
                        <input name="customer_name" value="{{ old('customer_name') }}" class="w-full rounded-lg border border-gray-300 px-4 py-2.5">
                        @error('customer_name')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label class="mb-1 block text-sm font-medium">Email</label>
                        <input name="customer_email" type="email" value="{{ old('customer_email') }}" class="w-full rounded-lg border border-gray-300 px-4 py-2.5">
                        @error('customer_email')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                    </div>
                </div>
                <div class="grid gap-4 sm:grid-cols-2">
                    <div>
                        <label class="mb-1 block text-sm font-medium">Số điện thoại</label>
                        <input name="customer_phone" value="{{ old('customer_phone') }}" class="w-full rounded-lg border border-gray-300 px-4 py-2.5">
                        @error('customer_phone')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label class="mb-1 block text-sm font-medium">Phương thức thanh toán</label>
                        <select name="payment_method" class="w-full rounded-lg border border-gray-300 px-4 py-2.5">
                            <option value="cod" @selected(old('payment_method', 'cod') === 'cod')>Thanh toán khi nhận hàng</option>
                            <option value="vnpay" @selected(old('payment_method') === 'vnpay')>VNPay</option>
                        </select>
                    </div>
                </div>
                <div>
                    <label class="mb-1 block text-sm font-medium">Địa chỉ giao hàng</label>
                    <textarea name="customer_address" rows="3" class="w-full rounded-lg border border-gray-300 px-4 py-2.5">{{ old('customer_address') }}</textarea>
                    @error('customer_address')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label class="mb-1 block text-sm font-medium">Ghi chú</label>
                    <textarea name="notes" rows="3" class="w-full rounded-lg border border-gray-300 px-4 py-2.5">{{ old('notes') }}</textarea>
                </div>
                <button class="rounded-lg bg-gray-900 px-5 py-3 font-medium text-white">Đặt hàng</button>
            </form>
        </section>

        <aside class="h-fit rounded-lg border border-gray-200 bg-white p-5">
            <h2 class="mb-4 font-semibold">Đơn hàng của bạn</h2>
            <div class="space-y-3">
                @foreach ($items as $item)
                    <div class="flex justify-between gap-3 text-sm">
                        <span>{{ $item['product']->name }} x {{ $item['quantity'] }}</span>
                        <span>{{ number_format($item['subtotal'], 0, ',', '.') }} đ</span>
                    </div>
                @endforeach
            </div>
            <div class="mt-4 flex justify-between border-t border-gray-200 pt-4 font-semibold">
                <span>Tổng cộng</span>
                <span>{{ number_format($total, 0, ',', '.') }} đ</span>
            </div>
        </aside>
    </div>
@endsection
