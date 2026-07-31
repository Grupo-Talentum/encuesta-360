<?php

namespace Tests\Feature;

use App\Actions\Nps\ResendNpsSurveyAction;
use App\Exceptions\NpsSurveyCannotBeSentException;
use App\Mail\NpsInvitationMail;
use App\Models\NpsSurvey;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class ResendNpsSurveyActionTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_rejects_survey_without_recipients(): void
    {
        $survey = NpsSurvey::create(['title' => 'NPS', 'question' => '¿Nos recomendarías?']);

        $this->expectExceptionMessage('No hay destinatarios para reenviar.');

        app(ResendNpsSurveyAction::class)->execute($survey);
    }

    public function test_it_resends_to_everyone_including_those_who_already_answered(): void
    {
        Mail::fake();

        $survey = NpsSurvey::create(['title' => 'NPS', 'question' => '¿Nos recomendarías?']);
        $answered = $survey->responses()->create([
            'name' => 'Juan',
            'email' => 'juan@test.com',
            'invited_at' => now()->subDay(),
            'score' => 9,
            'answered_at' => now()->subDay(),
        ]);
        $pending = $survey->responses()->create([
            'name' => 'Ana',
            'email' => 'ana@test.com',
            'invited_at' => now()->subDay(),
        ]);

        app(ResendNpsSurveyAction::class)->execute($survey);

        Mail::assertQueuedCount(2);
        Mail::assertQueued(NpsInvitationMail::class, fn (NpsInvitationMail $mail) => $mail->hasTo('juan@test.com'));
        Mail::assertQueued(NpsInvitationMail::class, fn (NpsInvitationMail $mail) => $mail->hasTo('ana@test.com'));

        $this->assertTrue($answered->fresh()->invited_at->greaterThan($answered->invited_at));
        $this->assertTrue($pending->fresh()->invited_at->greaterThan($pending->invited_at));
    }
}
