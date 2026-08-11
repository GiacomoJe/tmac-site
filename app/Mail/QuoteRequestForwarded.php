<?php

namespace App\Mail;

use App\Models\QuoteRequest;
use App\Models\Representative;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class QuoteRequestForwarded extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public QuoteRequest $request,
        public Representative $representative,
        public ?string $adminMessage = null,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: "[Cotação encaminhada] #{$this->request->code} — {$this->request->customer_name}",
            replyTo: [$this->request->email],
        );
    }

    public function content(): Content
    {
        return new Content(view: 'emails.quote-forwarded');
    }
}
