<?php

namespace App\Models;

use Database\Factories\OrderFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Order extends Model
{
    /** @use HasFactory<OrderFactory> */
    use HasFactory;

    protected $fillable = [
        'user_id',
        'customer_name',
        'customer_email',
        'customer_phone',
        'customer_address',
        'status',
        'subtotal',
        'total',
        'notes',
        'payment_method',
        'payment_status',
        'vnpay_txn_ref',
    ];

    protected function casts(): array
    {
        return [
            'subtotal' => 'decimal:2',
            'total' => 'decimal:2',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    public function statusLabel(): string
    {
        return match ($this->status) {
            'pending' => 'Chờ xác nhận',
            'processing' => 'Đang xử lý',
            'shipped' => 'Đang giao',
            'delivered' => 'Đã giao',
            'cancelled' => 'Đã hủy',
            default => $this->status,
        };
    }

    public function statusColor(): string
    {
        return match ($this->status) {
            'pending' => 'warning',
            'processing' => 'info',
            'shipped' => 'primary',
            'delivered' => 'success',
            'cancelled' => 'danger',
            default => 'secondary',
        };
    }

    public function paymentMethodLabel(): string
    {
        return match ($this->payment_method) {
            'cod' => 'Thanh toán khi nhận hàng',
            'vnpay' => 'VNPay',
            default => $this->payment_method,
        };
    }

    public function paymentStatusLabel(): string
    {
        return match ($this->payment_status) {
            'unpaid' => 'Chưa thanh toán',
            'paid' => 'Đã thanh toán',
            'refunded' => 'Đã hoàn tiền',
            default => $this->payment_status,
        };
    }

    public function formattedTotal(): string
    {
        return number_format($this->total, 0, ',', '.').' đ';
    }
}
