<?php

namespace App\Mail;

use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

/**
 * Sent when an admin sets or changes a user's custom daily quiz target
 * (App\Http\Controllers\Admin\UserController::update — roadmap §6.2).
 */
class TargetAssignedMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public function __construct(
        public readonly User $user,
        public readonly int $target,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Your daily quiz target has been updated',
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.target_assigned',
        );
    }
}
