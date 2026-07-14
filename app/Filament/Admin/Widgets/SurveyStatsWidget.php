<?php

namespace App\Filament\Admin\Widgets;

use App\Enums\EvaluationStatus;
use App\Enums\SurveyStatus;
use App\Models\Employee;
use App\Models\Evaluation;
use App\Models\Survey;
use App\Models\Team;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class SurveyStatsWidget extends StatsOverviewWidget
{
    protected function getStats(): array
    {
        $totalEvaluations = Evaluation::count();
        $completedEvaluations = Evaluation::where('status', EvaluationStatus::Completed)->count();
        $participation = $totalEvaluations > 0
            ? round($completedEvaluations / $totalEvaluations * 100, 1)
            : 0;

        return [
            Stat::make('Encuestas activas', Survey::where('status', SurveyStatus::Published)->count())
                ->color('success'),
            Stat::make('Encuestas cerradas', Survey::where('status', SurveyStatus::Closed)->count())
                ->color('gray'),
            Stat::make('Participantes', Employee::count()),
            Stat::make('Equipos', Team::count()),
            Stat::make('Evaluaciones pendientes', Evaluation::where('status', EvaluationStatus::Pending)->count())
                ->color('warning'),
            Stat::make('Evaluaciones completadas', $completedEvaluations)
                ->color('success'),
            Stat::make('Participación', "{$participation}%")
                ->description("{$completedEvaluations} de {$totalEvaluations} evaluaciones"),
        ];
    }
}
