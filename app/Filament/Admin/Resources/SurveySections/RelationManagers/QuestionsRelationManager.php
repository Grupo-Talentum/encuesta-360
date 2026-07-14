<?php

namespace App\Filament\Admin\Resources\SurveySections\RelationManagers;

use App\Enums\QuestionType;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TagsInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class QuestionsRelationManager extends RelationManager
{
    protected static string $relationship = 'questions';

    protected static ?string $title = 'Preguntas';

    public function form(Schema $schema): Schema
    {
        return $schema->components([
            TextInput::make('title')
                ->label('Título')
                ->required()
                ->maxLength(255),
            Textarea::make('description')
                ->label('Descripción')
                ->rows(2),
            Select::make('type')
                ->label('Tipo')
                ->options(array_combine(
                    array_map(fn (QuestionType $type) => $type->value, QuestionType::cases()),
                    array_map(fn (QuestionType $type) => $type->label(), QuestionType::cases()),
                ))
                ->live()
                ->required(),
            TagsInput::make('options')
                ->label('Opciones')
                ->helperText('Escribí cada opción y presioná Enter.')
                ->visible(fn (Get $get) => QuestionType::tryFrom($get('type'))?->hasOptions())
                ->required(fn (Get $get) => QuestionType::tryFrom($get('type'))?->hasOptions()),
            TextInput::make('order')
                ->label('Orden')
                ->numeric()
                ->default(0),
            Toggle::make('is_required')
                ->label('Obligatoria')
                ->default(true),
        ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('title')
            ->reorderable('order')
            ->defaultSort('order')
            ->columns([
                TextColumn::make('order')->label('Orden'),
                TextColumn::make('title')->label('Título'),
                TextColumn::make('type')->label('Tipo')->badge()->formatStateUsing(fn (QuestionType $state) => $state->label()),
                IconColumn::make('is_required')->label('Obligatoria')->boolean(),
            ])
            ->headerActions([
                CreateAction::make(),
            ])
            ->recordActions([
                EditAction::make()->color('gray'),
                DeleteAction::make(),
            ]);
    }
}
