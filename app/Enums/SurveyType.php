<?php

namespace App\Enums;

enum SurveyType: string
{
    case Global = 'global';
    case SingleTeam = 'single_team';
    case TeamsToTeam = 'teams_to_team';
}
