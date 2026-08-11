<?php

namespace App\Mail;

use App\Models\QuoteRequest;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class QuoteRequestSubmitted extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public QuoteRequest $request) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: "Nova cotação #{$this->request->code} — ".$this->request->customer_name,
        );
    }

    public function content(): Content
    {
        return new Content(view: 'emails.quote-request');
    }
}
