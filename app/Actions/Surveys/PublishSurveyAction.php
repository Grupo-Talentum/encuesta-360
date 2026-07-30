<?php

namespace App\Actions\Surveys;

use App\Enums\RelationType;
use App\Enums\SurveyStatus;
use App\Enums\SurveyType;
use App\Exceptions\SurveyCannotBePublishedException;
use App\Models\EmployeeRelation;
use App\Models\Evaluation;
use App\Models\EvaluationSession;
use App\Models\Survey;
use App\Notifications\EvaluationInvitationNotification;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;

class PublishSurveyAction
{
    public function execute(Survey $survey): void
    {
        if ($survey->type === SurveyType::TeamsToTeam) {
            app(SyncTeamsToTeamRelationsAction::class)->execute($survey);
        }

        $this->validate($survey);

        $sessions = DB::transaction(function () use ($survey) {
            $sessions = $this->relationsFor($survey)
                ->groupBy('employee_id')
                ->map(function ($relations, $evaluatorId) use ($survey) {
                    $session = EvaluationSession::firstOrCreate([
                        'survey_id' => $survey->id,
                        'evaluator_id' => $evaluatorId,
                    ]);

                    $relations->sortBy(fn (EmployeeRelation $relation) => $relation->type === RelationType::Superior ? 1 : 0)
                        ->each(fn (EmployeeRelation $relation) => Evaluation::firstOrCreate([
                            'survey_id' => $survey->id,
                            'evaluator_id' => $relation->employee_id,
                            'evaluatee_id' => $relation->related_employee_id,
                        ], [
                            'evaluation_session_id' => $session->id,
                        ]));

                    return $session;
                })
                ->values();

            $survey->update(['status' => SurveyStatus::Published]);

            return $sessions;
        });

        $sessions->each(
            fn (EvaluationSession $session) => $session->evaluator->notify(new EvaluationInvitationNotification($session))
        );
    }

    private function relationsFor(Survey $survey): Collection
    {
        return match ($survey->type) {
            SurveyType::TeamsToTeam => $this->teamsToTeamRelationsFor($survey),
            default => EmployeeRelation::query()
                ->when(
                    $survey->team_id,
                    fn ($query) => $query->whereHas('employee', fn ($q) => $q->where('team_id', $survey->team_id))
                )
                ->get(),
        };
    }

    private function teamsToTeamRelationsFor(Survey $survey): Collection
    {
        $internal = EmployeeRelation::query()
            ->whereHas('employee', fn ($q) => $q->where('team_id', $survey->team_id))
            ->get();

        $evaluatorTeamIds = $survey->evaluatorTeams()->pluck('teams.id');

        $crossTeam = EmployeeRelation::query()
            ->where('type', RelationType::TeamsToTeam)
            ->whereHas('employee', fn ($q) => $q->whereIn('team_id', $evaluatorTeamIds))
            ->whereHas('relatedEmployee', fn ($q) => $q->where('team_id', $survey->team_id))
            ->get();

        return $internal->concat($crossTeam);
    }

    private function validate(Survey $survey): void
    {
        if ($survey->status !== SurveyStatus::Draft) {
            throw new SurveyCannotBePublishedException('Solo se pueden publicar encuestas en borrador.');
        }

        if ($survey->sections()->count() === 0) {
            throw new SurveyCannotBePublishedException('La encuesta no tiene secciones.');
        }

        if ($survey->questions()->count() === 0) {
            throw new SurveyCannotBePublishedException('La encuesta no tiene preguntas.');
        }

        if ($survey->type === SurveyType::TeamsToTeam) {
            if (! $survey->team_id) {
                throw new SurveyCannotBePublishedException('Debe seleccionar el equipo evaluado.');
            }

            if ($survey->evaluatorTeams()->count() === 0) {
                throw new SurveyCannotBePublishedException('Debe seleccionar al menos un equipo evaluador.');
            }
        }

        if ($this->relationsFor($survey)->isEmpty()) {
            throw new SurveyCannotBePublishedException('No hay relaciones definidas entre participantes.');
        }
    }
}
