<?php

namespace App\Actions\Nps;

use App\Exceptions\NpsSurveyCannotBeSentException;
use App\Mail\NpsInvitationMail;
use App\Models\NpsSurvey;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;

class ResendNpsSurveyAction
{
    public function execute(NpsSurvey $survey): void
    {
        $responses = $survey->responses;

        if ($responses->isEmpty()) {
            throw new NpsSurveyCannotBeSentException('No hay destinatarios para reenviar.');
        }

        DB::transaction(function () use ($responses) {
            $responses->each(function ($response) {
                Mail::to($response->email, $response->name)->queue(new NpsInvitationMail($response));
                $response->update(['invited_at' => now()]);
            });
        });
    }
}
