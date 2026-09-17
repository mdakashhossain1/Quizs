<?php

namespace App\Mail;

use App\Models\User;
use App\Models\UserDailyProgress;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

/**
 * Sent the moment a user's daily quiz target newly reaches 'completed'
 * (App\Services\TargetService::recordCompletedQuiz — fires exactly once
 * per day, same guard as the XP award).
 */
class TargetCompletedMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public function __construct(
        public readonly User $user,
        public readonly UserDailyProgress $progress,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: "Today's quiz target complete!",
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.target_completed',
        );
    }
}
