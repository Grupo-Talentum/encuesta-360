<?php

namespace App\Filament\Admin\Resources\NpsSurveys;

use App\Filament\Admin\Resources\NpsSurveys\Pages\CreateNpsSurvey;
use App\Filament\Admin\Resources\NpsSurveys\Pages\EditNpsSurvey;
use App\Filament\Admin\Resources\NpsSurveys\Pages\ListNpsSurveys;
use App\Filament\Admin\Resources\NpsSurveys\Pages\NpsSurveyResults;
use App\Models\NpsSurvey;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class NpsSurveyResource extends Resource
{
    protected static ?string $model = NpsSurvey::class;

    protected static string|\BackedEnum|null $navigationIcon = Heroicon::OutlinedFaceSmile;

    protected static ?string $navigationLabel = 'Encuestas NPS';

    protected static string|\UnitEnum|null $navigationGroup = 'NPS';

    protected static ?int $navigationSort = 10;

    protected static ?string $modelLabel = 'encuesta NPS';

    protected static ?string $pluralModelLabel = 'encuestas NPS';

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            TextInput::make('title')
                ->label('Título')
                ->required()
                ->maxLength(255),
            Textarea::make('question')
                ->label('Pregunta')
                ->required()
                ->rows(2)
                ->helperText('Ej: ¿Qué probabilidad hay de que nos recomiendes a un amigo o colega?'),
            Repeater::make('responses')
                ->label('Destinatarios')
                ->relationship('responses')
                ->schema([
                    TextInput::make('name')
                        ->label('Nombre')
                        ->required(),
                    TextInput::make('email')
                        ->label('Email')
                        ->email()
                        ->required(),
                ])
                ->columns(2)
                ->addActionLabel('Agregar destinatario')
                ->helperText('Puedes seguir agregando destinatarios después de enviar la campaña; el botón "Enviar" solo les manda el email a los nuevos.'),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('title')->label('Título')->searchable(),
                TextColumn::make('status')->label('Estado')->badge(),
                TextColumn::make('responses_count')->label('Destinatarios')->counts('responses'),
                TextColumn::make('sent_at')->label('Enviada')->dateTime(),
            ])
            ->defaultSort('created_at', 'desc');
    }

    public static function getPages(): array
    {
        return [
            'index' => ListNpsSurveys::route('/'),
            'create' => CreateNpsSurvey::route('/create'),
            'edit' => EditNpsSurvey::route('/{record}/edit'),
            'results' => NpsSurveyResults::route('/{record}/results'),
        ];
    }
}
