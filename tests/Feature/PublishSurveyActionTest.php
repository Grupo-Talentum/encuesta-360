<?php

namespace Tests\Feature;

use App\Actions\Surveys\PublishSurveyAction;
use App\Enums\QuestionType;
use App\Enums\RelationType;
use App\Enums\SurveyStatus;
use App\Enums\SurveyType;
use App\Exceptions\SurveyCannotBePublishedException;
use App\Models\Employee;
use App\Models\EmployeeRelation;
use App\Models\Evaluation;
use App\Models\EvaluationSession;
use App\Models\Survey;
use App\Models\SurveyQuestion;
use App\Models\SurveySection;
use App\Models\Team;
use App\Notifications\EvaluationInvitationNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class PublishSurveyActionTest extends TestCase
{
    use RefreshDatabase;

    private function surveyWithSectionAndQuestion(): Survey
    {
        $survey = Survey::create(['title' => 'Encuesta 360']);
        $section = SurveySection::create(['survey_id' => $survey->id, 'title' => 'Colaboracion', 'order' => 1]);
        SurveyQuestion::create(['survey_section_id' => $section->id, 'title' => 'Comunica bien?', 'type' => QuestionType::Rating10, 'order' => 1]);

        return $survey;
    }

    public function test_it_rejects_survey_that_is_not_draft(): void
    {
        $survey = $this->surveyWithSectionAndQuestion();
        $survey->update(['status' => SurveyStatus::Published]);

        $this->expectException(SurveyCannotBePublishedException::class);

        app(PublishSurveyAction::class)->execute($survey);
    }

    public function test_it_rejects_survey_without_sections(): void
    {
        $survey = Survey::create(['title' => 'Encuesta 360']);

        $this->expectExceptionMessage('La encuesta no tiene secciones.');

        app(PublishSurveyAction::class)->execute($survey);
    }

    public function test_it_rejects_survey_without_questions(): void
    {
        $survey = Survey::create(['title' => 'Encuesta 360']);
        SurveySection::create(['survey_id' => $survey->id, 'title' => 'Colaboracion', 'order' => 1]);

        $this->expectExceptionMessage('La encuesta no tiene preguntas.');

        app(PublishSurveyAction::class)->execute($survey);
    }

    public function test_it_rejects_survey_without_employee_relations(): void
    {
        $survey = $this->surveyWithSectionAndQuestion();

        $this->expectExceptionMessage('No hay relaciones definidas entre participantes.');

        app(PublishSurveyAction::class)->execute($survey);
    }

    public function test_it_generates_evaluations_and_queues_invitations(): void
    {
        Notification::fake();

        $juan = Employee::create(['name' => 'Juan', 'email' => 'juan@test.com']);
        $carlos = Employee::create(['name' => 'Carlos', 'email' => 'carlos@test.com']);

        EmployeeRelation::create(['employee_id' => $juan->id, 'related_employee_id' => $carlos->id, 'type' => RelationType::Superior]);
        EmployeeRelation::create(['employee_id' => $carlos->id, 'related_employee_id' => $juan->id, 'type' => RelationType::Subordinate]);

        $survey = $this->surveyWithSectionAndQuestion();

        app(PublishSurveyAction::class)->execute($survey);

        $survey->refresh();
        $this->assertSame(SurveyStatus::Published, $survey->status);

        $this->assertDatabaseHas('evaluations', [
            'survey_id' => $survey->id,
            'evaluator_id' => $juan->id,
            'evaluatee_id' => $carlos->id,
        ]);
        $this->assertDatabaseHas('evaluations', [
            'survey_id' => $survey->id,
            'evaluator_id' => $carlos->id,
            'evaluatee_id' => $juan->id,
        ]);

        $juanEvaluation = Evaluation::where('evaluator_id', $juan->id)->first();
        $this->assertNotEmpty($juanEvaluation->uuid);

        $this->assertDatabaseCount('evaluation_sessions', 2);

        Notification::assertSentTo($juan, EvaluationInvitationNotification::class);
        Notification::assertSentTo($carlos, EvaluationInvitationNotification::class);
    }

    public function test_it_groups_evaluations_into_one_session_per_evaluator_with_peers_before_superior(): void
    {
        Notification::fake();

        $juan = Employee::create(['name' => 'Juan', 'email' => 'juan@test.com']);
        $pedro = Employee::create(['name' => 'Pedro', 'email' => 'pedro@test.com']);
        $pepe = Employee::create(['name' => 'Pepe', 'email' => 'pepe@test.com']);
        $carlos = Employee::create(['name' => 'Carlos', 'email' => 'carlos@test.com']);

        // Juan tiene 3 compañeros y 1 superior: debe generar 1 sola sesion con 4 evaluaciones,
        // compañeros primero y superior al final.
        EmployeeRelation::create(['employee_id' => $juan->id, 'related_employee_id' => $pedro->id, 'type' => RelationType::Peer]);
        EmployeeRelation::create(['employee_id' => $juan->id, 'related_employee_id' => $carlos->id, 'type' => RelationType::Superior]);
        EmployeeRelation::create(['employee_id' => $juan->id, 'related_employee_id' => $pepe->id, 'type' => RelationType::Peer]);

        $survey = $this->surveyWithSectionAndQuestion();

        app(PublishSurveyAction::class)->execute($survey);

        $sessions = EvaluationSession::where('survey_id', $survey->id)->where('evaluator_id', $juan->id)->get();
        $this->assertCount(1, $sessions);

        $session = $sessions->first();
        $orderedEvaluatees = $session->evaluations()->with('evaluatee')->get()->pluck('evaluatee.name');

        $this->assertCount(3, $orderedEvaluatees);
        $this->assertSame('Carlos', $orderedEvaluatees->last());
        $this->assertEqualsCanonicalizing(['Pedro', 'Pepe'], $orderedEvaluatees->take(2)->all());

        Notification::assertSentToTimes($juan, EvaluationInvitationNotification::class, 1);
    }

    public function test_survey_scoped_to_a_team_only_generates_evaluations_for_that_team(): void
    {
        Notification::fake();

        $teamA = Team::create(['name' => 'Producto']);
        $teamB = Team::create(['name' => 'Ventas']);

        $juan = Employee::create(['name' => 'Juan', 'email' => 'juan@test.com', 'team_id' => $teamA->id]);
        $carlos = Employee::create(['name' => 'Carlos', 'email' => 'carlos@test.com', 'team_id' => $teamA->id]);
        $luis = Employee::create(['name' => 'Luis', 'email' => 'luis@test.com', 'team_id' => $teamB->id]);
        $ana = Employee::create(['name' => 'Ana', 'email' => 'ana@test.com', 'team_id' => $teamB->id]);

        EmployeeRelation::create(['employee_id' => $juan->id, 'related_employee_id' => $carlos->id, 'type' => RelationType::Peer]);
        EmployeeRelation::create(['employee_id' => $luis->id, 'related_employee_id' => $ana->id, 'type' => RelationType::Peer]);

        $survey = $this->surveyWithSectionAndQuestion();
        $survey->update(['team_id' => $teamA->id]);

        app(PublishSurveyAction::class)->execute($survey);

        $this->assertDatabaseHas('evaluations', [
            'survey_id' => $survey->id,
            'evaluator_id' => $juan->id,
            'evaluatee_id' => $carlos->id,
        ]);
        $this->assertDatabaseMissing('evaluations', [
            'survey_id' => $survey->id,
            'evaluator_id' => $luis->id,
        ]);

        Notification::assertSentTo($juan, EvaluationInvitationNotification::class);
        Notification::assertNotSentTo($luis, EvaluationInvitationNotification::class);
    }

    public function test_teams_to_team_survey_rejects_without_evaluator_teams(): void
    {
        $team = Team::create(['name' => 'Producto']);
        $survey = $this->surveyWithSectionAndQuestion();
        $survey->update(['type' => SurveyType::TeamsToTeam, 'team_id' => $team->id]);

        $this->expectExceptionMessage('Debe seleccionar al menos un equipo evaluador.');

        app(PublishSurveyAction::class)->execute($survey);
    }

    public function test_teams_to_team_survey_generates_cross_team_and_internal_evaluations(): void
    {
        Notification::fake();

        $evaluated = Team::create(['name' => 'Producto']);
        $evaluatorTeam = Team::create(['name' => 'Ventas']);
        $otherTeam = Team::create(['name' => 'Soporte']);

        $juan = Employee::create(['name' => 'Juan', 'email' => 'juan@test.com', 'team_id' => $evaluated->id]);
        $carlos = Employee::create(['name' => 'Carlos', 'email' => 'carlos@test.com', 'team_id' => $evaluated->id]);
        $luis = Employee::create(['name' => 'Luis', 'email' => 'luis@test.com', 'team_id' => $evaluatorTeam->id]);
        $ana = Employee::create(['name' => 'Ana', 'email' => 'ana@test.com', 'team_id' => $otherTeam->id]);

        EmployeeRelation::create(['employee_id' => $juan->id, 'related_employee_id' => $carlos->id, 'type' => RelationType::Peer]);

        $survey = $this->surveyWithSectionAndQuestion();
        $survey->update(['type' => SurveyType::TeamsToTeam, 'team_id' => $evaluated->id]);
        $survey->evaluatorTeams()->sync([$evaluatorTeam->id]);

        app(PublishSurveyAction::class)->execute($survey);

        // Interna: Juan sigue evaluando a Carlos dentro del equipo evaluado.
        $this->assertDatabaseHas('evaluations', [
            'survey_id' => $survey->id,
            'evaluator_id' => $juan->id,
            'evaluatee_id' => $carlos->id,
        ]);

        // Cruzada: Luis (equipo evaluador) evalúa a Juan y Carlos (equipo evaluado).
        $this->assertDatabaseHas('evaluations', [
            'survey_id' => $survey->id,
            'evaluator_id' => $luis->id,
            'evaluatee_id' => $juan->id,
        ]);
        $this->assertDatabaseHas('evaluations', [
            'survey_id' => $survey->id,
            'evaluator_id' => $luis->id,
            'evaluatee_id' => $carlos->id,
        ]);

        // Ana (equipo no evaluador) no participa.
        $this->assertDatabaseMissing('evaluations', [
            'survey_id' => $survey->id,
            'evaluator_id' => $ana->id,
        ]);

        Notification::assertSentTo($luis, EvaluationInvitationNotification::class);
        Notification::assertNotSentTo($ana, EvaluationInvitationNotification::class);
    }
}
