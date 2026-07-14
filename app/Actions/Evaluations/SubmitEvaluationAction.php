<?php

namespace App\Actions\Evaluations;

use App\Enums\EvaluationStatus;
use App\Models\Evaluation;
use App\Models\EvaluationAnswer;
use Illuminate\Support\Facades\DB;

class SubmitEvaluationAction
{
    /**
     * @param  array<int, mixed>  $answers  Keyed by survey_question_id.
     */
    public function execute(Evaluation $evaluation, array $answers): void
    {
        DB::transaction(function () use ($evaluation, $answers) {
            foreach ($answers as $questionId => $value) {
                EvaluationAnswer::updateOrCreate(
                    ['evaluation_id' => $evaluation->id, 'survey_question_id' => $questionId],
                    ['value' => $value],
                );
            }

            $evaluation->update([
                'status' => EvaluationStatus::Completed,
                'completed_at' => now(),
            ]);
        });
    }
}
