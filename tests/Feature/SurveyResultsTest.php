<?php

namespace Tests\Feature;

use App\Actions\Surveys\GetSurveyResultsAction;
use App\Enums\EvaluationStatus;
use App\Enums\QuestionType;
use App\Exports\SurveyResultsExport;
use App\Models\Employee;
use App\Models\Evaluation;
use App\Models\EvaluationAnswer;
use App\Models\Survey;
use App\Models\SurveyQuestion;
use App\Models\SurveySection;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SurveyResultsTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->actingAs(User::factory()->create());
    }

    private function buildScenario(): Survey
    {
        $survey = Survey::create(['title' => 'Encuesta 360']);
        $section = SurveySection::create(['survey_id' => $survey->id, 'title' => 'Colaboracion', 'order' => 1]);

        $rating = SurveyQuestion::create(['survey_section_id' => $section->id, 'title' => 'Comunica bien?', 'type' => QuestionType::Rating10, 'order' => 1]);
        $nps = SurveyQuestion::create(['survey_section_id' => $section->id, 'title' => 'Recomendarias?', 'type' => QuestionType::Nps, 'order' => 2]);
        $comment = SurveyQuestion::create(['survey_section_id' => $section->id, 'title' => 'Comentario', 'type' => QuestionType::LongText, 'order' => 3, 'is_required' => false]);

        $juan = Employee::create(['name' => 'Juan', 'email' => 'juan@test.com']);
        $carlos = Employee::create(['name' => 'Carlos', 'email' => 'carlos@test.com']);
        $ana = Employee::create(['name' => 'Ana', 'email' => 'ana@test.com']);

        // Juan evalua a Carlos: completada, rating 8, nps 9 (promotor), comentario.
        $eval1 = Evaluation::create(['survey_id' => $survey->id, 'evaluator_id' => $juan->id, 'evaluatee_id' => $carlos->id]);
        EvaluationAnswer::create(['evaluation_id' => $eval1->id, 'survey_question_id' => $rating->id, 'value' => 8]);
        EvaluationAnswer::create(['evaluation_id' => $eval1->id, 'survey_question_id' => $nps->id, 'value' => 9]);
        EvaluationAnswer::create(['evaluation_id' => $eval1->id, 'survey_question_id' => $comment->id, 'value' => 'Muy buen trabajo']);
        $eval1->update(['status' => EvaluationStatus::Completed, 'completed_at' => now()]);

        // Ana evalua a Carlos: completada, rating 4, nps 3 (detractor).
        $eval2 = Evaluation::create(['survey_id' => $survey->id, 'evaluator_id' => $ana->id, 'evaluatee_id' => $carlos->id]);
        EvaluationAnswer::create(['evaluation_id' => $eval2->id, 'survey_question_id' => $rating->id, 'value' => 4]);
        EvaluationAnswer::create(['evaluation_id' => $eval2->id, 'survey_question_id' => $nps->id, 'value' => 3]);
        $eval2->update(['status' => EvaluationStatus::Completed, 'completed_at' => now()]);

        // Carlos evalua a Juan: pendiente (no cuenta para promedios).
        Evaluation::create(['survey_id' => $survey->id, 'evaluator_id' => $carlos->id, 'evaluatee_id' => $juan->id]);

        return $survey;
    }

    public function test_action_computes_correct_aggregates(): void
    {
        $survey = $this->buildScenario();

        $results = app(GetSurveyResultsAction::class)->execute($survey);

        $this->assertSame(3, $results['total']);
        $this->assertSame(2, $results['completed']);
        $this->assertSame(0, $results['skipped']);
        $this->assertSame(1, $results['pending']);
        $this->assertSame(66.7, $results['participation']);

        $ratingAverage = $results['questionAverages']->firstWhere('question.title', 'Comunica bien?');
        $this->assertSame(6.0, $ratingAverage['average']); // (8+4)/2

        $carlosAverage = $results['employeeAverages']->firstWhere('employee.name', 'Carlos');
        $this->assertSame(6.0, $carlosAverage['average']); // promedio de rating+nps de Carlos: (8+9+4+3)/4 = 6

        // NPS: 1 promotor (9), 1 detractor (3) de 2 respuestas -> (1-1)/2*100 = 0
        $this->assertSame(0.0, $results['npsScore']);

        $this->assertCount(1, $results['comments']);
        $this->assertSame('Muy buen trabajo', $results['comments']->first()['text']);
    }

    public function test_skipped_evaluations_are_excluded_from_pending_and_averages(): void
    {
        $survey = $this->buildScenario();

        $ana = Employee::where('name', 'Ana')->first();
        $luis = Employee::create(['name' => 'Luis', 'email' => 'luis@test.com']);

        Evaluation::create([
            'survey_id' => $survey->id,
            'evaluator_id' => $ana->id,
            'evaluatee_id' => $luis->id,
            'status' => EvaluationStatus::Skipped,
            'completed_at' => now(),
        ]);

        $results = app(GetSurveyResultsAction::class)->execute($survey);

        $this->assertSame(4, $results['total']);
        $this->assertSame(2, $results['completed']);
        $this->assertSame(1, $results['skipped']);
        $this->assertSame(1, $results['pending']);

        $this->assertNull($results['employeeAverages']->firstWhere('employee.name', 'Luis'));
    }

    public function test_results_page_loads(): void
    {
        $survey = $this->buildScenario();

        $this->get("/admin/surveys/{$survey->id}/results")
            ->assertOk()
            ->assertSee('Resultados: Encuesta 360')
            ->assertSee('Muy buen trabajo')
            ->assertSeeInOrder(['Carlos', 'Evaluado por Juan', 'Evaluado por Ana']);
    }

    public function test_dashboard_loads(): void
    {
        $this->buildScenario();

        $this->get('/admin')->assertOk();
    }

    public function test_export_contains_one_row_per_answer_and_pending_evaluations(): void
    {
        $survey = $this->buildScenario();

        $rows = (new SurveyResultsExport($survey))->collection();

        // eval1 (Juan->Carlos): 3 respuestas, eval2 (Ana->Carlos): 2 respuestas, eval3 (Carlos->Juan) pendiente: 1 fila vacia.
        $this->assertCount(6, $rows);

        $pendingRow = $rows->firstWhere(fn ($row) => $row[0] === 'Carlos');
        $this->assertSame('Juan', $pendingRow[1]);
        $this->assertSame('pending', $pendingRow[7]);
        $this->assertNull($pendingRow[4]);

        $commentRow = $rows->first(fn ($row) => $row[6] === 'Muy buen trabajo');
        $this->assertSame('Juan', $commentRow[0]);
        $this->assertSame('Carlos', $commentRow[1]);
        $this->assertSame('completed', $commentRow[7]);
    }
}
