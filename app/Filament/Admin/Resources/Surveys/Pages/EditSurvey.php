<?php

namespace App\Filament\Admin\Resources\Surveys\Pages;

use App\Actions\Surveys\PublishSurveyAction;
use App\Enums\SurveyStatus;
use App\Exceptions\SurveyCannotBePublishedException;
use App\Filament\Admin\Resources\Surveys\SurveyResource;
use App\Models\Survey;
use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;
use Filament\Support\Icons\Heroicon;

class EditSurvey extends EditRecord
{
    protected static string $resource = SurveyResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('results')
                ->label('Ver resultados')
                ->icon(Heroicon::OutlinedChartBar)
                ->color('gray')
                ->url(fn (Survey $record) => SurveyResource::getUrl('results', ['record' => $record])),
            Action::make('publish')
                ->label('Publicar')
                ->icon(Heroicon::OutlinedPaperAirplane)
                ->color('success')
                ->visible(fn (Survey $record) => $record->status === SurveyStatus::Draft)
                ->requiresConfirmation()
                ->modalDescription('Se generarán las evaluaciones y se enviarán los emails de invitación. Esta acción no se puede deshacer.')
                ->action(function (Survey $record) {
                    try {
                        app(PublishSurveyAction::class)->execute($record);

                        Notification::make()
                            ->title('Encuesta publicada')
                            ->success()
                            ->send();
                    } catch (SurveyCannotBePublishedException $exception) {
                        Notification::make()
                            ->title('No se pudo publicar')
                            ->body($exception->getMessage())
                            ->danger()
                            ->send();
                    }
                }),
            DeleteAction::make(),
        ];
    }
}
