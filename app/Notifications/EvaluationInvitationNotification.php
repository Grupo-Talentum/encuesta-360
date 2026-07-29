<?php

namespace App\Notifications;

use App\Mail\EvaluationInvitationMail;
use App\Models\Employee;
use App\Models\EvaluationSession;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
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

    public function toMail(Employee $notifiable): EvaluationInvitationMail
    {
        return (new EvaluationInvitationMail($this->session, $notifiable))
            ->to($notifiable->email, $notifiable->name);
    }
}
