@extends('layouts.app')

@section('content')
    <x-common.page-breadcrumb pageTitle="Quản lý đơn hàng" />

    <div class="space-y-6">
        @session('success')
            <x-ui.alert variant="success">{{ $value }}</x-ui.alert>
        @endsession

        {{-- Filters --}}
        <div class="rounded-xl border border-gray-200 bg-white p-5 dark:border-gray-800 dark:bg-white/[0.03]">
            <form method="GET" class="flex flex-wrap items-end gap-4">
                <div>
                    <label class="mb-1 block text-xs font-medium text-gray-500 dark:text-gray-400">Trạng thái</label>
                    <select name="status"
                        class="rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm focus:border-brand-500 focus:outline-none dark:border-gray-700 dark:bg-gray-800 dark:text-white">
                        <option value="">Tất cả</option>
                        @foreach ($statuses as $value => $label)
                            <option value="{{ $value }}" @selected(request('status') === $value)>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="mb-1 block text-xs font-medium text-gray-500 dark:text-gray-400">Từ ngày</label>
                    <input type="date" name="date_from" value="{{ request('date_from') }}"
                        class="rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm focus:border-brand-500 focus:outline-none dark:border-gray-700 dark:bg-gray-800 dark:text-white" />
                </div>
                <div>
                    <label class="mb-1 block text-xs font-medium text-gray-500 dark:text-gray-400">Đến ngày</label>
                    <input type="date" name="date_to" value="{{ request('date_to') }}"
                        class="rounded-lg border border-gray-300 bg-white px-3 py-2 text-sm focus:border-brand-500 focus:outline-none dark:border-gray-700 dark:bg-gray-800 dark:text-white" />
                </div>
                <div class="flex gap-2">
                    <button type="submit"
                        class="rounded-lg bg-brand-500 px-4 py-2 text-sm font-medium text-white hover:bg-brand-600">
                        Lọc
                    </button>
                    @if (request()->hasAny(['status', 'date_from', 'date_to']))
                        <a href="{{ route('orders.index') }}"
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
                                <p class="font-medium text-gray-500 text-theme-xs dark:text-gray-400">#</p>
                            </th>
                            <th class="px-5 py-3 text-left">
                                <p class="font-medium text-gray-500 text-theme-xs dark:text-gray-400">Khách hàng</p>
                            </th>
                            <th class="px-5 py-3 text-left">
                                <p class="font-medium text-gray-500 text-theme-xs dark:text-gray-400">Ngày đặt</p>
                            </th>
                            <th class="px-5 py-3 text-left">
                                <p class="font-medium text-gray-500 text-theme-xs dark:text-gray-400">Trạng thái</p>
                            </th>
                            <th class="px-5 py-3 text-right">
                                <p class="font-medium text-gray-500 text-theme-xs dark:text-gray-400">Tổng tiền</p>
                            </th>
                            <th class="px-5 py-3 text-left">
                                <p class="font-medium text-gray-500 text-theme-xs dark:text-gray-400">Thanh toán</p>
                            </th>
                            <th class="px-5 py-3 text-left">
                                <p class="font-medium text-gray-500 text-theme-xs dark:text-gray-400">Thao tác</p>
                            </th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($orders as $order)
                            @php
                                $statusColors = [
                                    'pending' => 'bg-warning-50 text-warning-700 dark:bg-warning-500/15 dark:text-warning-400',
                                    'processing' => 'bg-blue-50 text-blue-700 dark:bg-blue-500/15 dark:text-blue-400',
                                    'shipped' => 'bg-indigo-50 text-indigo-700 dark:bg-indigo-500/15 dark:text-indigo-400',
                                    'delivered' => 'bg-success-50 text-success-700 dark:bg-success-500/15 dark:text-success-400',
                                    'cancelled' => 'bg-red-50 text-red-700 dark:bg-red-500/15 dark:text-red-400',
                                ];
                            @endphp
                            <tr class="border-b border-gray-100 dark:border-gray-800">
                                <td class="px-5 py-4">
                                    <span class="font-mono font-medium text-gray-800 text-theme-sm dark:text-white/90">#{{ $order->id }}</span>
                                </td>
                                <td class="px-5 py-4">
                                    <p class="font-medium text-gray-800 text-theme-sm dark:text-white/90">{{ $order->customer_name }}</p>
                                    <p class="text-gray-400 text-theme-xs">{{ $order->customer_email }}</p>
                                </td>
                                <td class="px-5 py-4">
                                    <p class="text-gray-500 text-theme-sm dark:text-gray-400">{{ $order->created_at->format('d/m/Y H:i') }}</p>
                                </td>
                                <td class="px-5 py-4">
                                    <span class="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-medium {{ $statusColors[$order->status] ?? 'bg-gray-100 text-gray-600' }}">
                                        {{ $order->statusLabel() }}
                                    </span>
                                </td>
                                <td class="px-5 py-4 text-right">
                                    <p class="font-medium text-gray-800 text-theme-sm dark:text-white/90">{{ $order->formattedTotal() }}</p>
                                </td>
                                <td class="px-5 py-4">
                                    <p class="text-gray-500 text-theme-sm dark:text-gray-400">{{ $order->paymentMethodLabel() }}</p>
                                    @if ($order->payment_status === 'paid')
                                        <span class="text-xs text-success-600 dark:text-success-400">Đã thanh toán</span>
                                    @endif
                                </td>
                                <td class="px-5 py-4">
                                    <a href="{{ route('orders.show', $order) }}"
                                        class="inline-flex items-center rounded-lg border border-gray-300 bg-white px-3 py-1.5 text-sm font-medium text-gray-700 hover:bg-gray-50 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-400">
                                        Xem
                                    </a>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="px-5 py-10 text-center">
                                    <p class="text-gray-500 dark:text-gray-400">Chưa có đơn hàng nào.</p>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        @if ($orders->hasPages())
            <div class="mt-4">
                {{ $orders->links() }}
            </div>
        @endif
    </div>
@endsection
