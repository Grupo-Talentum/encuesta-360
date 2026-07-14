<?php

namespace Tests\Feature;

use App\Enums\EvaluationStatus;
use App\Enums\QuestionType;
use App\Enums\SurveyStatus;
use App\Livewire\SurveyResponse;
use App\Models\Employee;
use App\Models\Evaluation;
use App\Models\EvaluationSession;
use App\Models\Survey;
use App\Models\SurveyQuestion;
use App\Models\SurveySection;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
use Livewire\Livewire;
use Tests\TestCase;

class SurveyResponseTest extends TestCase
{
    use RefreshDatabase;

    private function publishedSurveyWithQuestions(): Survey
    {
        $survey = Survey::create(['title' => 'Encuesta 360', 'status' => SurveyStatus::Published, 'end_message' => 'Gracias por participar.']);
        $section = SurveySection::create(['survey_id' => $survey->id, 'title' => 'Colaboracion', 'order' => 1]);

        SurveyQuestion::create(['survey_section_id' => $section->id, 'title' => 'Comunica bien?', 'type' => QuestionType::Rating10, 'order' => 1, 'is_required' => true]);
        SurveyQuestion::create(['survey_section_id' => $section->id, 'title' => 'Comentario', 'type' => QuestionType::LongText, 'order' => 2, 'is_required' => false]);
        SurveyQuestion::create(['survey_section_id' => $section->id, 'title' => 'Frecuencia', 'type' => QuestionType::SingleChoice, 'options' => ['Nunca', 'A veces', 'Siempre'], 'order' => 3, 'is_required' => true]);

        return $survey;
    }

    /**
     * @param  array<int, string>  $evaluateeNames
     */
    private function sessionFor(Survey $survey, string $evaluatorName, array $evaluateeNames): EvaluationSession
    {
        $evaluator = Employee::create(['name' => $evaluatorName, 'email' => strtolower($evaluatorName) . '@test.com']);

        $session = EvaluationSession::create(['survey_id' => $survey->id, 'evaluator_id' => $evaluator->id]);

        foreach ($evaluateeNames as $name) {
            $evaluatee = Employee::create(['name' => $name, 'email' => strtolower($name) . '@test.com']);

            Evaluation::create([
                'survey_id' => $survey->id,
                'evaluation_session_id' => $session->id,
                'evaluator_id' => $evaluator->id,
                'evaluatee_id' => $evaluatee->id,
            ]);
        }

        return $session;
    }

    public function test_invalid_uuid_shows_invalid_state(): void
    {
        Livewire::test(SurveyResponse::class, ['uuid' => (string) Str::uuid()])
            ->assertSet('step', 'invalid');
    }

    public function test_fully_completed_session_shows_done_state(): void
    {
        $survey = $this->publishedSurveyWithQuestions();
        $session = $this->sessionFor($survey, 'Juan', ['Carlos']);
        $session->evaluations->first()->update(['status' => EvaluationStatus::Completed, 'completed_at' => now()]);

        Livewire::test(SurveyResponse::class, ['uuid' => $session->uuid])
            ->assertSet('step', 'done');
    }

    public function test_single_evaluatee_flow(): void
    {
        $survey = $this->publishedSurveyWithQuestions();
        $session = $this->sessionFor($survey, 'Juan', ['Carlos']);
        $questions = SurveyQuestion::orderBy('order')->get();

        Livewire::test(SurveyResponse::class, ['uuid' => $session->uuid])
            ->assertSet('step', 'intro')
            ->call('start')
            ->assertSet('step', 'form')
            ->set("answers.{$questions[0]->id}", 8)
            ->set("answers.{$questions[2]->id}", 'Siempre')
            ->call('submit')
            ->assertSet('step', 'done')
            ->assertHasNoErrors();

        $evaluation = $session->evaluations->first()->fresh();
        $this->assertSame(EvaluationStatus::Completed, $evaluation->status);
        $this->assertNotNull($evaluation->completed_at);
    }

    public function test_start_and_submit_dispatch_scroll_to_top(): void
    {
        $survey = $this->publishedSurveyWithQuestions();
        $session = $this->sessionFor($survey, 'Juan', ['Carlos']);
        $questions = SurveyQuestion::orderBy('order')->get();

        Livewire::test(SurveyResponse::class, ['uuid' => $session->uuid])
            ->call('start')
            ->assertDispatched('survey-scroll-top')
            ->set("answers.{$questions[0]->id}", 8)
            ->set("answers.{$questions[2]->id}", 'Siempre')
            ->call('submit')
            ->assertDispatched('survey-scroll-top');
    }

    public function test_multiple_evaluatees_are_answered_in_sequence_and_progress_is_saved(): void
    {
        $survey = $this->publishedSurveyWithQuestions();
        $session = $this->sessionFor($survey, 'Juan', ['Pedro', 'Pepe', 'Carlos']);
        $questions = SurveyQuestion::orderBy('order')->get();

        $component = Livewire::test(SurveyResponse::class, ['uuid' => $session->uuid])
            ->assertSet('step', 'intro')
            ->call('start')
            ->assertSet('step', 'form');

        // Contesta a Pedro (primer evaluado).
        $this->assertSame('Pedro', $component->get('currentEvaluation')->evaluatee->name);
        $component
            ->set("answers.{$questions[0]->id}", 7)
            ->set("answers.{$questions[2]->id}", 'A veces')
            ->call('submit')
            ->assertHasNoErrors()
            ->assertSet('step', 'form');

        // Contesta a Pepe (segundo evaluado).
        $this->assertSame('Pepe', $component->get('currentEvaluation')->evaluatee->name);
        $component
            ->set("answers.{$questions[0]->id}", 9)
            ->set("answers.{$questions[2]->id}", 'Siempre')
            ->call('submit')
            ->assertHasNoErrors()
            ->assertSet('step', 'form');

        // Simula que el navegador se cierra y se reabre el mismo link: debe retomar en Carlos (superior), no reiniciar.
        $resumed = Livewire::test(SurveyResponse::class, ['uuid' => $session->uuid]);
        $resumed->assertSet('step', 'form');
        $this->assertSame('Carlos', $resumed->get('currentEvaluation')->evaluatee->name);

        $resumed
            ->set("answers.{$questions[0]->id}", 6)
            ->set("answers.{$questions[2]->id}", 'Nunca')
            ->call('submit')
            ->assertSet('step', 'done')
            ->assertHasNoErrors();

        $completed = $session->evaluations()->where('status', EvaluationStatus::Completed)->count();
        $this->assertSame(3, $completed);
    }

    public function test_scale_legend_is_extracted_from_instructions_and_shown_in_form_step(): void
    {
        $survey = $this->publishedSurveyWithQuestions();
        $survey->update([
            'instructions' => "Escala del 1 al 10.\n\n1-3 indica un desempeño muy por debajo de lo esperado.\n4-6 indica un desempeño aceptable con margen de mejora.\n7-8 indica buen desempeño.\n9-10 indica un desempeño excelente.",
        ]);
        $session = $this->sessionFor($survey, 'Juan', ['Carlos']);

        $component = Livewire::test(SurveyResponse::class, ['uuid' => $session->uuid])
            ->call('start');

        $legend = $component->get('scaleLegend');

        $this->assertCount(4, $legend);
        $this->assertSame('text-red-600', $legend[0]['color']);
        $this->assertSame('text-amber-600', $legend[1]['color']);
        $this->assertSame('text-indigo-600', $legend[2]['color']);
        $this->assertSame('text-emerald-600', $legend[3]['color']);

        $component->assertSee('Escala de valoración');
        $component->assertSee('9-10 indica un desempeño excelente.');
    }

    public function test_required_question_is_validated(): void
    {
        $survey = $this->publishedSurveyWithQuestions();
        $session = $this->sessionFor($survey, 'Juan', ['Carlos']);

        Livewire::test(SurveyResponse::class, ['uuid' => $session->uuid])
            ->call('start')
            ->call('submit')
            ->assertHasErrors();

        $this->assertSame(EvaluationStatus::Pending, $session->evaluations->first()->fresh()->status);
    }

    public function test_route_resolves_to_component(): void
    {
        $survey = $this->publishedSurveyWithQuestions();
        $session = $this->sessionFor($survey, 'Juan', ['Carlos']);

        $this->get(route('survey.show', $session->uuid))->assertOk();
    }
}
