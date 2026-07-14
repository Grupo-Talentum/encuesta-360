<?php

namespace App\Exports;

use App\Models\Survey;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class SurveyResultsExport implements FromCollection, WithHeadings
{
    public function __construct(private readonly Survey $survey)
    {
    }

    public function headings(): array
    {
        return [
            'Evaluador',
            'Evaluado',
            'Equipo evaluado',
            'Sección',
            'Pregunta',
            'Tipo',
            'Respuesta',
            'Estado',
            'Completada',
        ];
    }

    public function collection(): Collection
    {
        $evaluations = $this->survey->evaluations()
            ->with(['evaluator', 'evaluatee.team', 'answers.question.section'])
            ->get();

        $rows = collect();

        foreach ($evaluations as $evaluation) {
            if ($evaluation->answers->isEmpty()) {
                $rows->push($this->row($evaluation, null));

                continue;
            }

            foreach ($evaluation->answers as $answer) {
                $rows->push($this->row($evaluation, $answer));
            }
        }

        return $rows;
    }

    private function row($evaluation, $answer): array
    {
        $value = $answer?->value;

        return [
            $evaluation->evaluator->name,
            $evaluation->evaluatee->name,
            $evaluation->evaluatee->team?->name,
            $answer?->question->section->title,
            $answer?->question->title,
            $answer?->question->type->label(),
            is_array($value) ? implode(', ', $value) : $value,
            $evaluation->status->value,
            $evaluation->completed_at?->format('d/m/Y H:i'),
        ];
    }
}
