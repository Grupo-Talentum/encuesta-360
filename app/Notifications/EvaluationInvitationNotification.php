<?php

namespace App\Notifications;

use App\Models\EvaluationSession;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class EvaluationInvitationNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(private readonly EvaluationSession $session)
    {
    }

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $survey = $this->session->survey;
        $evaluatees = $this->session->evaluations->pluck('evaluatee.name')->implode(', ');

        return (new MailMessage)
            ->subject("Nueva evaluación disponible: {$survey->title}")
            ->greeting("Hola {$notifiable->name}.")
            ->line('Tienes una nueva evaluación disponible.')
            ->line("Vas a evaluar a: {$evaluatees}")
            ->action('Responder encuesta', route('survey.show', $this->session->uuid));
    }
}
