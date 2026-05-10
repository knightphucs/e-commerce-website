<x-mail::message>
# Cập nhật trạng thái đơn hàng

Xin chào **{{ $order->customer_name }}**,

Đơn hàng **#{{ $order->id }}** của bạn đã được cập nhật trạng thái mới.

<x-mail::panel>
**Trạng thái mới:** {{ $order->statusLabel() }}
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

Nếu bạn có thắc mắc, vui lòng liên hệ chúng tôi qua email hoặc hotline.

Trân trọng,<br>
{{ config('app.name') }}
</x-mail::message>
