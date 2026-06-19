<x-mail::message>
# Cảm ơn bạn đã đặt hàng

Xin chào **{{ $order->customer_name }}**,

Chúng tôi đã nhận được đơn hàng **#{{ $order->id }}** của bạn.

<x-mail::panel>
**Mã theo dõi đơn hàng:** {{ $order->tracking_code }}<br>
**Phương thức thanh toán:** {{ $order->paymentMethodLabel() }}
</x-mail::panel>

## Chi tiết đơn hàng

<x-mail::table>
| Sản phẩm | Số lượng | Đơn giá | Thành tiền |
|:---------|:--------:|--------:|----------:|
@foreach ($order->items as $item)
| {{ $item->product?->name ?? 'Sản phẩm đã xóa' }} | {{ $item->quantity }} | {{ number_format($item->unit_price, 0, ',', '.') }} đ | {{ number_format($item->quantity * $item->unit_price, 0, ',', '.') }} đ |
@endforeach
</x-mail::table>

**Tổng cộng: {{ $order->formattedTotal() }}**

---

**Thông tin giao hàng:**
- Người nhận: {{ $order->customer_name }}
- Email: {{ $order->customer_email }}
@if ($order->customer_phone)
- Điện thoại: {{ $order->customer_phone }}
@endif
@if ($order->customer_address)
- Địa chỉ: {{ $order->customer_address }}
@endif

<x-mail::button :url="route('storefront.orders.show', ['order' => $order->tracking_code])">
Theo dõi đơn hàng
</x-mail::button>

Trân trọng,<br>
{{ config('app.name') }}
</x-mail::message>
