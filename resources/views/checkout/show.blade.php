@extends('layouts.storefront')

@section('content')
    <div class="grid gap-6 lg:grid-cols-[1fr_320px]">
        <section class="rounded-lg border border-gray-200 bg-white p-6">
            <p class="text-sm text-gray-500">Mã theo dõi</p>
            <h1 class="mt-1 text-2xl font-semibold">{{ $order->tracking_code }}</h1>
            <div class="mt-6 space-y-3">
                @foreach ($order->items as $item)
                    <div class="flex justify-between gap-4 border-b border-gray-100 pb-3">
                        <div>
                            <p class="font-medium">{{ $item->product?->name ?? 'Sản phẩm' }}</p>
                            <p class="text-sm text-gray-500">Số lượng: {{ $item->quantity }}</p>
                        </div>
                        <p class="font-medium">{{ $item->formattedSubtotal() }}</p>
                    </div>
                @endforeach
            </div>
        </section>
        <aside class="h-fit rounded-lg border border-gray-200 bg-white p-5">
            <div class="space-y-3 text-sm">
                <div class="flex justify-between"><span>Trạng thái đơn</span><span class="font-medium">{{ $order->statusLabel() }}</span></div>
                <div class="flex justify-between"><span>Thanh toán</span><span class="font-medium">{{ $order->paymentMethodLabel() }}</span></div>
                <div class="flex justify-between"><span>Trạng thái thanh toán</span><span class="font-medium">{{ $order->paymentStatusLabel() }}</span></div>
            </div>
            <div class="mt-4 flex justify-between border-t border-gray-200 pt-4 font-semibold">
                <span>Tổng cộng</span>
                <span>{{ $order->formattedTotal() }}</span>
            </div>
            @if ($order->payment_method === 'vnpay' && $order->payment_status !== 'paid')
                <a href="{{ route('payment.checkout', ['order' => $order->tracking_code]) }}" class="mt-5 block rounded-lg bg-gray-900 px-4 py-3 text-center font-medium text-white">Thanh toán VNPay</a>
            @endif
        </aside>
    </div>
@endsection
