<?php

namespace App\Mail;

use App\Models\NpsResponse;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class NpsInvitationMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public function __construct(public readonly NpsResponse $response) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Talentum Voice | Escuchamos para mejorar ⭐',
        );
    }

    public function content(): Content
    {
        $scoreLinks = collect(range(0, 10))->mapWithKeys(fn (int $score) => [
            $score => route('nps.respond', ['token' => $this->response->token, 'score' => $score]),
        ]);

        return new Content(
            view: 'emails.nps-invitation',
            with: [
                'name' => $this->response->name,
                'question' => $this->response->npsSurvey->question,
                'scoreLinks' => $scoreLinks,
            ],
        );
    }
}
