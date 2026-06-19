@extends('layouts.app')

@section('content')
    <x-common.page-breadcrumb pageTitle="Chi tiết đơn hàng #{{ $order->id }}" />

    <div class="space-y-6">
        @session('success')
            <x-ui.alert variant="success">{{ $value }}</x-ui.alert>
        @endsession
        @session('error')
            <x-ui.alert variant="danger">{{ $value }}</x-ui.alert>
        @endsession

        <div class="grid grid-cols-1 gap-6 lg:grid-cols-3">
            {{-- Left: Order Detail --}}
            <div class="space-y-6 lg:col-span-2">
                {{-- Order Items --}}
                <div class="overflow-hidden rounded-xl border border-gray-200 bg-white dark:border-gray-800 dark:bg-white/[0.03]">
                    <div class="border-b border-gray-200 px-6 py-4 dark:border-gray-800">
                        <h3 class="font-medium text-gray-800 dark:text-white/90">Sản phẩm trong đơn</h3>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="w-full min-w-[500px]">
                            <thead>
                                <tr class="border-b border-gray-100 dark:border-gray-800">
                                    <th class="px-5 py-3 text-left">
                                        <p class="font-medium text-gray-500 text-theme-xs dark:text-gray-400">Sản phẩm</p>
                                    </th>
                                    <th class="px-5 py-3 text-right">
                                        <p class="font-medium text-gray-500 text-theme-xs dark:text-gray-400">Đơn giá</p>
                                    </th>
                                    <th class="px-5 py-3 text-right">
                                        <p class="font-medium text-gray-500 text-theme-xs dark:text-gray-400">SL</p>
                                    </th>
                                    <th class="px-5 py-3 text-right">
                                        <p class="font-medium text-gray-500 text-theme-xs dark:text-gray-400">Thành tiền</p>
                                    </th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($order->items as $item)
                                    <tr class="border-b border-gray-100 dark:border-gray-800">
                                        <td class="px-5 py-4">
                                            <p class="font-medium text-gray-800 text-theme-sm dark:text-white/90">
                                                {{ $item->product?->name ?? 'Sản phẩm đã bị xóa' }}
                                            </p>
                                        </td>
                                        <td class="px-5 py-4 text-right">
                                            <p class="text-gray-500 text-theme-sm dark:text-gray-400">
                                                {{ number_format($item->unit_price, 0, ',', '.') }} đ
                                            </p>
                                        </td>
                                        <td class="px-5 py-4 text-right">
                                            <p class="text-gray-500 text-theme-sm dark:text-gray-400">{{ $item->quantity }}</p>
                                        </td>
                                        <td class="px-5 py-4 text-right">
                                            <p class="font-medium text-gray-800 text-theme-sm dark:text-white/90">
                                                {{ $item->formattedSubtotal() }}
                                            </p>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                            <tfoot>
                                <tr class="border-t border-gray-200 dark:border-gray-700">
                                    <td colspan="3" class="px-5 py-4 text-right font-medium text-gray-700 dark:text-gray-300">
                                        Tổng cộng:
                                    </td>
                                    <td class="px-5 py-4 text-right font-bold text-gray-900 dark:text-white">
                                        {{ $order->formattedTotal() }}
                                    </td>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>

                {{-- Update Status --}}
                <div class="rounded-xl border border-gray-200 bg-white p-6 dark:border-gray-800 dark:bg-white/[0.03]">
                    <h3 class="mb-4 font-medium text-gray-800 dark:text-white/90">Cập nhật trạng thái</h3>
                    <form action="{{ route('orders.updateStatus', $order) }}" method="POST" class="flex flex-wrap items-end gap-4">
                        @csrf
                        @method('PATCH')
                        <div class="flex-1">
                            <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">Trạng thái mới</label>
                            <select name="status"
                                class="w-full rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-sm focus:border-brand-500 focus:outline-none dark:border-gray-700 dark:bg-gray-800 dark:text-white">
                                @foreach ($statuses as $value => $label)
                                    <option value="{{ $value }}" @selected($order->status === $value)>{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>
                        <button type="submit"
                            class="rounded-lg bg-brand-500 px-5 py-2.5 text-sm font-medium text-white hover:bg-brand-600">
                            Cập nhật & Gửi email
                        </button>
                    </form>
                </div>
            </div>

            {{-- Right: Customer Info & Order Summary --}}
            <div class="space-y-6">
                {{-- Current Status --}}
                <div class="rounded-xl border border-gray-200 bg-white p-6 dark:border-gray-800 dark:bg-white/[0.03]">
                    <h3 class="mb-3 font-medium text-gray-800 dark:text-white/90">Trạng thái đơn hàng</h3>
                    @php
                        $statusColors = [
                            'pending' => 'bg-warning-50 text-warning-700 dark:bg-warning-500/15 dark:text-warning-400',
                            'processing' => 'bg-blue-50 text-blue-700 dark:bg-blue-500/15 dark:text-blue-400',
                            'shipped' => 'bg-indigo-50 text-indigo-700 dark:bg-indigo-500/15 dark:text-indigo-400',
                            'delivered' => 'bg-success-50 text-success-700 dark:bg-success-500/15 dark:text-success-400',
                            'cancelled' => 'bg-red-50 text-red-700 dark:bg-red-500/15 dark:text-red-400',
                        ];
                    @endphp
                    <span class="inline-flex items-center rounded-full px-3 py-1 text-sm font-medium {{ $statusColors[$order->status] ?? '' }}">
                        {{ $order->statusLabel() }}
                    </span>
                    <p class="mt-2 text-xs text-gray-400">Đặt lúc: {{ $order->created_at->format('H:i d/m/Y') }}</p>
                </div>

                {{-- Status Timeline --}}
                <div class="rounded-xl border border-gray-200 bg-white p-6 dark:border-gray-800 dark:bg-white/[0.03]">
                    <h3 class="mb-4 font-medium text-gray-800 dark:text-white/90">Lịch sử trạng thái</h3>
                    <ol class="space-y-4">
                        @foreach ($order->statusHistories as $history)
                            <li class="relative flex gap-3 pl-1">
                                @unless ($loop->last)
                                    <span class="absolute top-5 left-[7px] h-full w-px bg-gray-200 dark:bg-gray-700"></span>
                                @endunless
                                <span class="mt-1.5 h-3.5 w-3.5 shrink-0 rounded-full {{ $loop->last ? 'bg-brand-500' : 'bg-gray-300 dark:bg-gray-600' }}"></span>
                                <div>
                                    <p class="text-sm font-medium text-gray-800 dark:text-white/90">{{ $history->statusLabel() }}</p>
                                    <p class="text-xs text-gray-400">{{ $history->paymentStatusLabel() }} · {{ $history->created_at->format('H:i d/m/Y') }}</p>
                                </div>
                            </li>
                        @endforeach
                    </ol>
                </div>

                {{-- Customer Info --}}
                <div class="rounded-xl border border-gray-200 bg-white p-6 dark:border-gray-800 dark:bg-white/[0.03]">
                    <h3 class="mb-4 font-medium text-gray-800 dark:text-white/90">Thông tin khách hàng</h3>
                    <div class="space-y-3">
                        <div>
                            <p class="text-xs text-gray-400">Họ tên</p>
                            <p class="font-medium text-gray-800 dark:text-white/90">{{ $order->customer_name }}</p>
                        </div>
                        <div>
                            <p class="text-xs text-gray-400">Email</p>
                            <p class="text-gray-600 dark:text-gray-300">{{ $order->customer_email }}</p>
                        </div>
                        @if ($order->customer_phone)
                            <div>
                                <p class="text-xs text-gray-400">Điện thoại</p>
                                <p class="text-gray-600 dark:text-gray-300">{{ $order->customer_phone }}</p>
                            </div>
                        @endif
                        @if ($order->customer_address)
                            <div>
                                <p class="text-xs text-gray-400">Địa chỉ</p>
                                <p class="text-gray-600 dark:text-gray-300">{{ $order->customer_address }}</p>
                            </div>
                        @endif
                    </div>
                </div>

                {{-- Payment Info --}}
                <div class="rounded-xl border border-gray-200 bg-white p-6 dark:border-gray-800 dark:bg-white/[0.03]">
                    <h3 class="mb-4 font-medium text-gray-800 dark:text-white/90">Thanh toán</h3>
                    <div class="space-y-2">
                        <div class="flex justify-between">
                            <span class="text-sm text-gray-500 dark:text-gray-400">Phương thức:</span>
                            <span class="text-sm font-medium text-gray-800 dark:text-white/90">{{ $order->paymentMethodLabel() }}</span>
                        </div>
                        <div class="flex justify-between">
                            <span class="text-sm text-gray-500 dark:text-gray-400">Trạng thái:</span>
                            <span class="text-sm font-medium {{ $order->payment_status === 'paid' ? 'text-success-600 dark:text-success-400' : 'text-gray-600 dark:text-gray-300' }}">
                                {{ $order->paymentStatusLabel() }}
                            </span>
                        </div>
                        <div class="flex justify-between border-t border-gray-100 pt-2 dark:border-gray-700">
                            <span class="font-medium text-gray-700 dark:text-gray-300">Tổng cộng:</span>
                            <span class="font-bold text-gray-900 dark:text-white">{{ $order->formattedTotal() }}</span>
                        </div>
                    </div>

                    <form action="{{ route('orders.updatePaymentStatus', $order) }}" method="POST"
                        class="mt-4 space-y-3 border-t border-gray-100 pt-4 dark:border-gray-700">
                        @csrf
                        @method('PATCH')
                        <div>
                            <label class="mb-1.5 block text-sm font-medium text-gray-700 dark:text-gray-400">
                                Cập nhật trạng thái thanh toán
                            </label>
                            <select name="payment_status"
                                class="w-full rounded-lg border border-gray-300 bg-white px-4 py-2.5 text-sm focus:border-brand-500 focus:outline-none dark:border-gray-700 dark:bg-gray-800 dark:text-white">
                                @foreach ($paymentStatuses as $value => $label)
                                    <option value="{{ $value }}" @selected($order->payment_status === $value)>{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>
                        <button type="submit"
                            class="w-full rounded-lg bg-brand-500 py-2.5 text-sm font-medium text-white hover:bg-brand-600">
                            Cập nhật thanh toán
                        </button>
                    </form>
                </div>

                <a href="{{ route('orders.index') }}"
                    class="inline-flex items-center gap-2 text-sm text-gray-500 hover:text-gray-700 dark:text-gray-400 dark:hover:text-gray-200">
                    ← Quay lại danh sách
                </a>
            </div>
        </div>
    </div>
@endsection
