<?php

namespace App\Mail;

use App\Models\Order;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class OrderStatusUpdated extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public Order $order) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: "Cập nhật đơn hàng #{$this->order->id} - {$this->order->statusLabel()}",
        );
    }

    public function content(): Content
    {
        return new Content(
            markdown: 'emails.order-status-updated',
            with: ['order' => $this->order],
        );
    }
}
