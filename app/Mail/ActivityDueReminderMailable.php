<?php

namespace App\Mail;

use App\Models\Activity;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class ActivityDueReminderMailable extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public function __construct(
        public Activity $activity,
        public string $viewUrl,
        public string $headingLine,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: $this->headingLine,
        );
    }

    public function content(): Content
    {
        return new Content(
            markdown: 'mail.activity-due-reminder',
        );
    }

    /**
     * @return array<int, \Illuminate\Mail\Mailables\Attachment>
     */
    public function attachments(): array
    {
        return [];
    }
}
