<?php

namespace App\Actions\Surveys;

use App\Enums\EvaluationStatus;
use App\Enums\QuestionType;
use App\Models\Survey;

class GetSurveyResultsAction
{
    /**
     * @return array{
     *     total: int,
     *     completed: int,
     *     skipped: int,
     *     pending: int,
     *     participation: float,
     *     questionAverages: \Illuminate\Support\Collection,
     *     employeeAverages: \Illuminate\Support\Collection,
     *     comments: \Illuminate\Support\Collection,
     *     npsScore: float|null,
     *     evaluations: \Illuminate\Support\Collection,
     * }
     */
    public function execute(Survey $survey): array
    {
        $evaluations = $survey->evaluations()
            ->with(['evaluator', 'evaluatee', 'answers.question'])
            ->get();

        $completedEvaluations = $evaluations->where('status', EvaluationStatus::Completed);

        $answers = $completedEvaluations->flatMap(
            fn ($evaluation) => $evaluation->answers->map(fn ($answer) => [
                'answer' => $answer,
                'evaluation' => $evaluation,
            ])
        );

        $numericAnswers = $answers->filter(
            fn ($item) => in_array($item['answer']->question->type, [
                QuestionType::Rating5,
                QuestionType::Rating10,
                QuestionType::Nps,
            ])
        );

        $questionAverages = $numericAnswers
            ->groupBy(fn ($item) => $item['answer']->survey_question_id)
            ->map(function ($group) {
                return [
                    'question' => $group->first()['answer']->question,
                    'average' => round($group->avg(fn ($item) => (float) $item['answer']->value), 2),
                    'count' => $group->count(),
                ];
            })
            ->values();

        $employeeAverages = $numericAnswers
            ->groupBy(fn ($item) => $item['evaluation']->evaluatee_id)
            ->map(function ($group) {
                return [
                    'employee' => $group->first()['evaluation']->evaluatee,
                    'average' => round($group->avg(fn ($item) => (float) $item['answer']->value), 2),
                    'count' => $group->count(),
                ];
            })
            ->sortByDesc('average')
            ->values();

        $comments = $answers
            ->filter(fn ($item) => in_array($item['answer']->question->type, [QuestionType::ShortText, QuestionType::LongText]))
            ->filter(fn ($item) => filled($item['answer']->value))
            ->map(fn ($item) => [
                'evaluator' => $item['evaluation']->evaluator,
                'evaluatee' => $item['evaluation']->evaluatee,
                'question' => $item['answer']->question,
                'text' => $item['answer']->value,
            ])
            ->values();

        $npsValues = $answers
            ->filter(fn ($item) => $item['answer']->question->type === QuestionType::Nps)
            ->map(fn ($item) => (int) $item['answer']->value);

        $npsScore = null;

        if ($npsValues->isNotEmpty()) {
            $promoters = $npsValues->filter(fn ($value) => $value >= 9)->count();
            $detractors = $npsValues->filter(fn ($value) => $value <= 6)->count();
            $npsScore = round(($promoters - $detractors) / $npsValues->count() * 100, 1);
        }

        $total = $evaluations->count();
        $completed = $completedEvaluations->count();
        $skipped = $evaluations->where('status', EvaluationStatus::Skipped)->count();

        $evaluationsByEvaluatee = $evaluations
            ->groupBy('evaluatee_id')
            ->map(fn ($group) => [
                'employee' => $group->first()->evaluatee,
                'evaluations' => $group->values(),
            ])
            ->sortBy(fn ($group) => $group['employee']->name)
            ->values();

        return [
            'total' => $total,
            'completed' => $completed,
            'skipped' => $skipped,
            'pending' => $total - $completed - $skipped,
            'participation' => $total > 0 ? round($completed / $total * 100, 1) : 0.0,
            'questionAverages' => $questionAverages,
            'employeeAverages' => $employeeAverages,
            'comments' => $comments,
            'npsScore' => $npsScore,
            'evaluations' => $evaluations,
            'evaluationsByEvaluatee' => $evaluationsByEvaluatee,
        ];
    }
}
