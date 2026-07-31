<?php

namespace App\Mail;

use App\Models\Employee;
use App\Models\EvaluationSession;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class EvaluationInvitationMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public readonly EvaluationSession $session,
        public readonly Employee $evaluator,
    ) {
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: $this->session->survey->title,
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.evaluation-invitation',
            with: [
                'evaluator' => $this->evaluator,
                'evaluatees' => $this->session->evaluations->pluck('evaluatee.name'),
                'url' => route('survey.show', $this->session->uuid),
            ],
        );
    }
}
