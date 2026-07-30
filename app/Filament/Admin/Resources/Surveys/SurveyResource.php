<?php

namespace App\Filament\Admin\Resources\Surveys;

use App\Actions\Surveys\DuplicateSurveyAction;
use App\Enums\SurveyStatus;
use App\Enums\SurveyType;
use App\Filament\Admin\Resources\Surveys\Pages\CreateSurvey;
use App\Filament\Admin\Resources\Surveys\Pages\EditSurvey;
use App\Filament\Admin\Resources\Surveys\Pages\ListSurveys;
use App\Filament\Admin\Resources\Surveys\Pages\SurveyResults;
use App\Filament\Admin\Resources\Surveys\RelationManagers\SectionsRelationManager;
use App\Models\Survey;
use Filament\Actions\Action;
use Filament\Actions\EditAction;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class SurveyResource extends Resource
{
    protected static ?string $model = Survey::class;

    protected static string|\BackedEnum|null $navigationIcon = Heroicon::OutlinedClipboardDocumentList;

    protected static ?string $navigationLabel = 'Encuestas';

    protected static ?int $navigationSort = 3;

    protected static ?string $modelLabel = 'encuesta';

    protected static ?string $pluralModelLabel = 'encuestas';

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            TextInput::make('title')
                ->label('Título')
                ->required()
                ->maxLength(255),
            Select::make('type')
                ->label('Tipo de encuesta')
                ->options([
                    SurveyType::Global->value => 'Todos los equipos (360 completo)',
                    SurveyType::SingleTeam->value => 'Un solo equipo (360 dentro del equipo)',
                    SurveyType::TeamsToTeam->value => 'Equipos evalúan a un equipo (no 360)',
                ])
                ->default(SurveyType::Global->value)
                ->required()
                ->live(),
            Select::make('team_id')
                ->label('Equipo evaluado')
                ->relationship('team', 'name')
                ->searchable()
                ->preload()
                ->visible(fn (Get $get) => $get('type') !== SurveyType::Global->value)
                ->required(fn (Get $get) => $get('type') !== SurveyType::Global->value)
                ->helperText('Al publicar solo se generan evaluaciones entre sus integrantes.'),
            Select::make('evaluatorTeams')
                ->label('Equipos evaluadores')
                ->relationship('evaluatorTeams', 'name')
                ->multiple()
                ->searchable()
                ->preload()
                ->visible(fn (Get $get) => $get('type') === SurveyType::TeamsToTeam->value)
                ->required(fn (Get $get) => $get('type') === SurveyType::TeamsToTeam->value)
                ->helperText('Los integrantes de estos equipos evaluarán al equipo evaluado.'),
            Textarea::make('description')
                ->label('Descripción')
                ->rows(3),
            Textarea::make('instructions')
                ->label('Instrucciones')
                ->rows(3),
            Textarea::make('start_message')
                ->label('Mensaje inicial')
                ->rows(3),
            Textarea::make('end_message')
                ->label('Mensaje final')
                ->rows(3),
            DateTimePicker::make('starts_at')
                ->label('Fecha inicio'),
            DateTimePicker::make('ends_at')
                ->label('Fecha fin'),
            Select::make('status')
                ->label('Estado')
                ->options([
                    SurveyStatus::Draft->value => 'Borrador',
                    SurveyStatus::Published->value => 'Publicada',
                    SurveyStatus::Closed->value => 'Cerrada',
                ])
                ->default(SurveyStatus::Draft->value)
                ->required(),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('title')->label('Título')->searchable(),
                TextColumn::make('type')->label('Tipo')->badge()->formatStateUsing(fn (SurveyType $state) => match ($state) {
                    SurveyType::Global => 'Todos los equipos',
                    SurveyType::SingleTeam => 'Un solo equipo',
                    SurveyType::TeamsToTeam => 'Equipos a un equipo',
                }),
                TextColumn::make('team.name')->label('Equipo evaluado')->badge()->placeholder('—'),
                TextColumn::make('status')->label('Estado')->badge(),
                TextColumn::make('starts_at')->label('Inicio')->dateTime()->sortable(),
                TextColumn::make('ends_at')->label('Fin')->dateTime()->sortable(),
            ])
            ->defaultSort('created_at', 'desc')
            ->recordActions([
                EditAction::make()->color('gray'),
                Action::make('duplicate')
                    ->label('Duplicar')
                    ->icon(Heroicon::OutlinedDocumentDuplicate)
                    ->color('warning')
                    ->schema([
                        Select::make('team_id')
                            ->label('Equipo para la copia')
                            ->relationship('team', 'name')
                            ->searchable()
                            ->preload()
                            ->helperText('La copia queda en borrador, con secciones y preguntas clonadas, lista para publicar para este equipo.'),
                    ])
                    ->action(function (Survey $record, array $data) {
                        $copy = app(DuplicateSurveyAction::class)->execute($record, $data['team_id'] ?? null);

                        Notification::make()
                            ->title('Encuesta duplicada')
                            ->success()
                            ->send();

                        return redirect(static::getUrl('edit', ['record' => $copy]));
                    }),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            SectionsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListSurveys::route('/'),
            'create' => CreateSurvey::route('/create'),
            'edit' => EditSurvey::route('/{record}/edit'),
            'results' => SurveyResults::route('/{record}/results'),
        ];
    }
}
