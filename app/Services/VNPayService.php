<?php

namespace App\Services;

use App\Models\Order;

class VNPayService
{
    private string $tmnCode;

    private string $hashSecret;

    private string $url;

    private string $returnUrl;

    public function __construct()
    {
        $this->tmnCode = config('services.vnpay.tmn_code', '');
        $this->hashSecret = config('services.vnpay.hash_secret', '');
        $this->url = config('services.vnpay.url', 'https://sandbox.vnpayment.vn/paymentv2/vpcpay.html');
        $this->returnUrl = config('services.vnpay.return_url', '');
    }

    public function createPaymentUrl(Order $order, string $ipAddr): string
    {
        $txnRef = $order->id.'_'.time();
        $order->update(['vnpay_txn_ref' => $txnRef]);

        $createDate = now('Asia/Ho_Chi_Minh');

        $params = [
            'vnp_Version' => '2.1.0',
            'vnp_Command' => 'pay',
            'vnp_TmnCode' => $this->tmnCode,
            'vnp_Amount' => (int) ($order->total * 100),
            'vnp_CurrCode' => 'VND',
            'vnp_TxnRef' => $txnRef,
            'vnp_OrderInfo' => 'Thanh toan don hang #'.$order->id,
            'vnp_OrderType' => 'other',
            'vnp_Locale' => 'vn',
            'vnp_ReturnUrl' => $this->returnUrl,
            'vnp_IpAddr' => $ipAddr,
            'vnp_CreateDate' => $createDate->format('YmdHis'),
            'vnp_ExpireDate' => $createDate->copy()->addMinutes(15)->format('YmdHis'),
        ];

        ksort($params);

        $queryString = http_build_query($params);
        $signature = hash_hmac('sha512', $queryString, $this->hashSecret);

        return $this->url.'?'.$queryString.'&vnp_SecureHash='.$signature;
    }

    public function verifyCallback(array $params): bool
    {
        $secureHash = $params['vnp_SecureHash'] ?? '';

        $filteredParams = collect($params)
            ->except(['vnp_SecureHash', 'vnp_SecureHashType'])
            ->toArray();

        ksort($filteredParams);

        $queryString = http_build_query($filteredParams);
        $expectedHash = hash_hmac('sha512', $queryString, $this->hashSecret);

        return hash_equals($expectedHash, $secureHash);
    }

    public function isPaymentSuccess(array $params): bool
    {
        return ($params['vnp_ResponseCode'] ?? '') === '00'
            && ($params['vnp_TransactionStatus'] ?? '') === '00';
    }
}
