<?php

namespace App\Actions\Evaluations;

use App\Enums\EvaluationStatus;
use App\Models\Evaluation;

class SkipEvaluationAction
{
    public function execute(Evaluation $evaluation): void
    {
        $evaluation->update([
            'status' => EvaluationStatus::Skipped,
            'completed_at' => now(),
        ]);
    }
}
