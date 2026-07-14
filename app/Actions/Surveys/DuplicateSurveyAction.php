<?php

namespace App\Actions\Surveys;

use App\Enums\SurveyStatus;
use App\Models\Survey;
use Illuminate\Support\Facades\DB;

class DuplicateSurveyAction
{
    public function execute(Survey $survey, ?int $teamId): Survey
    {
        return DB::transaction(function () use ($survey, $teamId) {
            $copy = Survey::create([
                'title' => "{$survey->title} (copia)",
                'team_id' => $teamId,
                'description' => $survey->description,
                'instructions' => $survey->instructions,
                'start_message' => $survey->start_message,
                'end_message' => $survey->end_message,
                'status' => SurveyStatus::Draft,
            ]);

            foreach ($survey->sections as $section) {
                $newSection = $copy->sections()->create([
                    'title' => $section->title,
                    'description' => $section->description,
                    'order' => $section->order,
                ]);

                foreach ($section->questions as $question) {
                    $newSection->questions()->create([
                        'title' => $question->title,
                        'description' => $question->description,
                        'type' => $question->type,
                        'options' => $question->options,
                        'order' => $question->order,
                        'is_required' => $question->is_required,
                    ]);
                }
            }

            return $copy;
        });
    }
}
