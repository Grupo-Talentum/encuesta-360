<?php

namespace App\Actions\Surveys;

use App\Enums\RelationType;
use App\Models\Employee;
use App\Models\EmployeeRelation;
use App\Models\Survey;

class SyncTeamsToTeamRelationsAction
{
    public function execute(Survey $survey): void
    {
        $evaluatorIds = Employee::whereIn('team_id', $survey->evaluatorTeams()->pluck('teams.id'))->pluck('id');
        $evaluateeIds = Employee::where('team_id', $survey->team_id)->pluck('id');

        foreach ($evaluatorIds as $evaluatorId) {
            foreach ($evaluateeIds as $evaluateeId) {
                EmployeeRelation::firstOrCreate([
                    'employee_id' => $evaluatorId,
                    'related_employee_id' => $evaluateeId,
                    'type' => RelationType::TeamsToTeam,
                ]);
            }
        }
    }
}
