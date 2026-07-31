<?php

namespace App\Filament\Admin\Resources\NpsSurveys\Pages;

use App\Actions\Nps\SendNpsSurveyAction;
use App\Enums\NpsSurveyStatus;
use App\Exceptions\NpsSurveyCannotBeSentException;
use App\Filament\Admin\Resources\NpsSurveys\NpsSurveyResource;
use App\Mail\NpsInvitationMail;
use App\Models\NpsResponse;
use App\Models\NpsSurvey;
use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;
use Filament\Support\Icons\Heroicon;

class EditNpsSurvey extends EditRecord
{
    protected static string $resource = NpsSurveyResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('results')
                ->label('Ver resultados')
                ->icon(Heroicon::OutlinedChartBar)
                ->color('gray')
                ->url(fn (NpsSurvey $record) => NpsSurveyResource::getUrl('results', ['record' => $record])),
            Action::make('preview')
                ->label('Previsualizar email')
                ->icon(Heroicon::OutlinedEye)
                ->color('gray')
                ->modalHeading('Previsualización del email')
                ->modalSubmitAction(false)
                ->modalCancelActionLabel('Cerrar')
                ->modalContent(function (NpsSurvey $record) {
                    $sample = (new NpsResponse([
                        'name' => 'Persona de ejemplo',
                        'email' => 'ejemplo@test.com',
                        'token' => 'preview',
                    ]))->setRelation('npsSurvey', $record);

                    $html = (new NpsInvitationMail($sample))->render();

                    return view('filament.admin.email-preview', ['html' => $html]);
                }),
            Action::make('send')
                ->label('Enviar')
                ->icon(Heroicon::OutlinedPaperAirplane)
                ->color('success')
                ->visible(fn (NpsSurvey $record) => $record->status === NpsSurveyStatus::Draft)
                ->requiresConfirmation()
                ->modalDescription('Se enviará el email a todos los destinatarios cargados. Esta acción no se puede deshacer.')
                ->action(function (NpsSurvey $record) {
                    try {
                        app(SendNpsSurveyAction::class)->execute($record);

                        Notification::make()
                            ->title('Encuesta enviada')
                            ->success()
                            ->send();
                    } catch (NpsSurveyCannotBeSentException $exception) {
                        Notification::make()
                            ->title('No se pudo enviar')
                            ->body($exception->getMessage())
                            ->danger()
                            ->send();
                    }
                }),
            DeleteAction::make(),
        ];
    }
}
