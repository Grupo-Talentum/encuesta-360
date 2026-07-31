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

        $this->expectExceptionMessage('No hay destinatarios nuevos para enviar.');

        app(SendNpsSurveyAction::class)->execute($survey);
    }

    public function test_it_rejects_when_everyone_was_already_invited(): void
    {
        $survey = NpsSurvey::create(['title' => 'NPS', 'question' => '¿Nos recomendarías?', 'status' => NpsSurveyStatus::Sent]);
        $survey->responses()->create(['name' => 'Juan', 'email' => 'juan@test.com', 'invited_at' => now()]);

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

        $this->assertNotNull($survey->responses()->where('email', 'juan@test.com')->first()->invited_at);
    }

    public function test_it_only_sends_to_newly_added_recipients_on_an_already_sent_survey(): void
    {
        Mail::fake();

        $survey = NpsSurvey::create(['title' => 'NPS', 'question' => '¿Nos recomendarías?', 'status' => NpsSurveyStatus::Sent]);
        $survey->responses()->create(['name' => 'Juan', 'email' => 'juan@test.com', 'invited_at' => now()]);
        $survey->responses()->create(['name' => 'Nuevo', 'email' => 'nuevo@test.com']);

        app(SendNpsSurveyAction::class)->execute($survey);

        Mail::assertQueuedCount(1);
        Mail::assertQueued(NpsInvitationMail::class, fn (NpsInvitationMail $mail) => $mail->hasTo('nuevo@test.com'));
    }
}
