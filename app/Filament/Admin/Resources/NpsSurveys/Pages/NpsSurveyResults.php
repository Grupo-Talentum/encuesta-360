<?php

namespace App\Filament\Admin\Resources\NpsSurveys\Pages;

use App\Filament\Admin\Resources\NpsSurveys\NpsSurveyResource;
use App\Models\NpsSurvey;
use Filament\Resources\Pages\Concerns\InteractsWithRecord;
use Filament\Resources\Pages\Page;

class NpsSurveyResults extends Page
{
    use InteractsWithRecord;

    protected static string $resource = NpsSurveyResource::class;

    protected string $view = 'filament.admin.resources.nps-surveys.pages.nps-survey-results';

    public array $results = [];

    public function mount(int|string $record): void
    {
        $this->record = $this->resolveRecord($record);

        $responses = $this->getRecord()->responses;
        $answered = $responses->whereNotNull('score');

        $this->results = [
            'total' => $responses->count(),
            'answered' => $answered->count(),
            'pending' => $responses->count() - $answered->count(),
            'npsScore' => $this->getRecord()->npsScore(),
            'promoters' => $answered->where('score', '>=', 9)->count(),
            'passives' => $answered->whereBetween('score', [7, 8])->count(),
            'detractors' => $answered->where('score', '<=', 6)->count(),
            'responses' => $responses,
        ];
    }

    public function getTitle(): string
    {
        return 'Resultados: '.$this->getRecord()->title;
    }

    public function hasResourceBreadcrumbs(): bool
    {
        return false;
    }

    public function getRecord(): NpsSurvey
    {
        return $this->record;
    }
}
