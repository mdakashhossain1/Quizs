<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class OtpMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public readonly string $code,
        public readonly int $ttlMinutes,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Your Quizs verification code',
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.otp',
        );
    }
}
