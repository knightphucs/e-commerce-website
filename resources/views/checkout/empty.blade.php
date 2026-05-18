@extends('layouts.storefront')

@section('content')
    <div class="rounded-lg border border-gray-200 bg-white p-8 text-center">
        <h1 class="text-2xl font-semibold">Chưa có sản phẩm để thanh toán</h1>
        <p class="mt-2 text-gray-600">Hãy thêm sản phẩm vào giỏ hàng trước khi tiếp tục.</p>
        <a href="{{ route('shop.index') }}" class="mt-5 inline-block rounded-lg bg-gray-900 px-4 py-2.5 text-white">Quay lại cửa hàng</a>
    </div>
@endsection
