<?php

namespace App\Filament\Admin\Resources\Surveys\Pages;

use App\Actions\Surveys\GetSurveyResultsAction;
use App\Exports\SurveyResultsExport;
use App\Filament\Admin\Resources\Surveys\SurveyResource;
use App\Models\Survey;
use Filament\Actions\Action;
use Filament\Resources\Pages\Concerns\InteractsWithRecord;
use Filament\Resources\Pages\Page;
use Filament\Support\Icons\Heroicon;
use Maatwebsite\Excel\Facades\Excel;

class SurveyResults extends Page
{
    use InteractsWithRecord;

    protected static string $resource = SurveyResource::class;

    protected string $view = 'filament.admin.resources.surveys.pages.survey-results';

    public array $results = [];

    public function mount(int | string $record): void
    {
        $this->record = $this->resolveRecord($record);
        $this->results = app(GetSurveyResultsAction::class)->execute($this->getRecord());
    }

    protected function getHeaderActions(): array
    {
        return [
            Action::make('exportExcel')
                ->label('Exportar Excel')
                ->icon(Heroicon::OutlinedArrowDownTray)
                ->action(fn () => Excel::download(
                    new SurveyResultsExport($this->getRecord()),
                    "resultados-{$this->getRecord()->id}.xlsx"
                )),
        ];
    }

    public function getTitle(): string
    {
        return 'Resultados: ' . $this->getRecord()->title;
    }

    public function hasResourceBreadcrumbs(): bool
    {
        return false;
    }

    public function getRecord(): Survey
    {
        return $this->record;
    }
}
