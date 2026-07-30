<?php

namespace Tests\Feature;

use App\Actions\Nps\SendNpsSurveyAction;
use App\Enums\NpsSurveyStatus;
use App\Exceptions\NpsSurveyCannotBeSentException;
use App\Mail\NpsInvitationMail;
use App\Models\NpsSurvey;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class SendNpsSurveyActionTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_rejects_survey_without_recipients(): void
    {
        $survey = NpsSurvey::create(['title' => 'NPS', 'question' => '¿Nos recomendarías?']);

        $this->expectExceptionMessage('Agregá al menos un destinatario antes de enviar.');

        app(SendNpsSurveyAction::class)->execute($survey);
    }

    public function test_it_rejects_survey_that_is_not_draft(): void
    {
        $survey = NpsSurvey::create(['title' => 'NPS', 'question' => '¿Nos recomendarías?', 'status' => NpsSurveyStatus::Sent]);
        $survey->responses()->create(['name' => 'Juan', 'email' => 'juan@test.com']);

        $this->expectException(NpsSurveyCannotBeSentException::class);

        app(SendNpsSurveyAction::class)->execute($survey);
    }

    public function test_it_queues_invitations_and_marks_survey_as_sent(): void
    {
        Mail::fake();

        $survey = NpsSurvey::create(['title' => 'NPS', 'question' => '¿Nos recomendarías?']);
        $survey->responses()->create(['name' => 'Juan', 'email' => 'juan@test.com']);
        $survey->responses()->create(['name' => 'Ana', 'email' => 'ana@test.com']);

        app(SendNpsSurveyAction::class)->execute($survey);

        $survey->refresh();
        $this->assertSame(NpsSurveyStatus::Sent, $survey->status);
        $this->assertNotNull($survey->sent_at);

        Mail::assertQueuedCount(2);
        Mail::assertQueued(NpsInvitationMail::class, fn (NpsInvitationMail $mail) => $mail->hasTo('juan@test.com'));
        Mail::assertQueued(NpsInvitationMail::class, fn (NpsInvitationMail $mail) => $mail->hasTo('ana@test.com'));
    }
}
