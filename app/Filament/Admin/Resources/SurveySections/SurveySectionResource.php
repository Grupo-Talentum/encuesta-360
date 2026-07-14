<?php

namespace App\Filament\Admin\Resources\SurveySections;

use App\Filament\Admin\Resources\SurveySections\Pages\EditSurveySection;
use App\Filament\Admin\Resources\SurveySections\RelationManagers\QuestionsRelationManager;
use App\Models\SurveySection;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;

class SurveySectionResource extends Resource
{
    protected static ?string $model = SurveySection::class;

    protected static ?string $modelLabel = 'sección';

    protected static ?string $pluralModelLabel = 'secciones';

    public static function shouldRegisterNavigation(): bool
    {
        return false;
    }

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            TextInput::make('title')
                ->label('Título')
                ->required()
                ->maxLength(255),
            Textarea::make('description')
                ->label('Descripción')
                ->rows(2),
            TextInput::make('order')
                ->label('Orden')
                ->numeric()
                ->default(0),
        ]);
    }

    public static function getRelations(): array
    {
        return [
            QuestionsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'edit' => EditSurveySection::route('/{record}/edit'),
        ];
    }
}
