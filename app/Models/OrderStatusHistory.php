<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OrderStatusHistory extends Model
{
    const UPDATED_AT = null;

    protected $fillable = [
        'order_id',
        'status',
        'payment_status',
    ];

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function statusLabel(): string
    {
        return (new Order(['status' => $this->status]))->statusLabel();
    }

    public function paymentStatusLabel(): string
    {
        return (new Order(['payment_status' => $this->payment_status]))->paymentStatusLabel();
    }
}
