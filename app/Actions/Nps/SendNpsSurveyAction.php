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
        $pending = $survey->responses()->whereNull('invited_at')->get();

        if ($pending->isEmpty()) {
            throw new NpsSurveyCannotBeSentException('No hay destinatarios nuevos para enviar.');
        }

        DB::transaction(function () use ($survey, $pending) {
            $pending->each(function ($response) {
                Mail::to($response->email, $response->name)->queue(new NpsInvitationMail($response));
                $response->update(['invited_at' => now()]);
            });

            if ($survey->status === NpsSurveyStatus::Draft) {
                $survey->update(['status' => NpsSurveyStatus::Sent, 'sent_at' => now()]);
            }
        });
    }
}
