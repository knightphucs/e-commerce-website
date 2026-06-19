<?php

namespace App\Mail;

use App\Models\Order;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class OrderPlaced extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public Order $order) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: "Xác nhận đơn hàng #{$this->order->id} - {$this->order->tracking_code}",
        );
    }

    public function content(): Content
    {
        return new Content(
            markdown: 'emails.order-placed',
            with: ['order' => $this->order],
        );
    }
}
