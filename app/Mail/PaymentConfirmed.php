<?php

namespace App\Mail;

use App\Models\Order;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class PaymentConfirmed extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public Order $order) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: "Thanh toán thành công - Đơn hàng #{$this->order->id}",
        );
    }

    public function content(): Content
    {
        return new Content(
            markdown: 'emails.payment-confirmed',
            with: ['order' => $this->order],
        );
    }
}
