<?php

namespace App\Mail;

use App\Models\Lead;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class LeadCaptured extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(public Lead $lead) {}

    public function envelope(): Envelope
    {
        $sourceLabel = $this->lead->source_label;
        return new Envelope(
            subject: "[Lead] {$sourceLabel} — {$this->lead->name}",
            replyTo: [$this->lead->email],
        );
    }

    public function content(): Content
    {
        return new Content(view: 'emails.lead-captured');
    }
}
