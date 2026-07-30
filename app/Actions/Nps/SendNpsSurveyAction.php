<?php

namespace App\Actions\Nps;

use App\Enums\NpsSurveyStatus;
use App\Exceptions\NpsSurveyCannotBeSentException;
use App\Mail\NpsInvitationMail;
use App\Models\NpsSurvey;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;

class SendNpsSurveyAction
{
    public function execute(NpsSurvey $survey): void
    {
        if ($survey->status !== NpsSurveyStatus::Draft) {
            throw new NpsSurveyCannotBeSentException('Solo se pueden enviar campañas en borrador.');
        }

        if ($survey->responses()->count() === 0) {
            throw new NpsSurveyCannotBeSentException('Agregá al menos un destinatario antes de enviar.');
        }

        DB::transaction(function () use ($survey) {
            $survey->responses->each(
                fn ($response) => Mail::to($response->email, $response->name)->queue(new NpsInvitationMail($response))
            );

            $survey->update(['status' => NpsSurveyStatus::Sent, 'sent_at' => now()]);
        });
    }
}
