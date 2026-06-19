<x-mail::message>
# Thanh toán thành công

Xin chào **{{ $order->customer_name }}**,

Chúng tôi đã nhận được thanh toán cho đơn hàng **#{{ $order->id }}** qua VNPay.

<x-mail::panel>
**Mã theo dõi đơn hàng:** {{ $order->tracking_code }}<br>
**Số tiền:** {{ $order->formattedTotal() }}<br>
**Trạng thái đơn hàng:** {{ $order->statusLabel() }}
</x-mail::panel>

Đơn hàng của bạn đang được xử lý và sẽ sớm được giao đến địa chỉ:
{{ $order->customer_address }}

<x-mail::button :url="route('storefront.orders.show', ['order' => $order->tracking_code])">
Theo dõi đơn hàng
</x-mail::button>

Trân trọng,<br>
{{ config('app.name') }}
</x-mail::message>
