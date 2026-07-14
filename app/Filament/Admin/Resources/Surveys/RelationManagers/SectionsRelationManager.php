<?php

namespace App\Filament\Admin\Resources\Surveys\RelationManagers;

use App\Filament\Admin\Resources\SurveySections\SurveySectionResource;
use Filament\Actions\Action;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class SectionsRelationManager extends RelationManager
{
    protected static string $relationship = 'sections';

    protected static ?string $title = 'Secciones';

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
            TextInput::make('order')
                ->label('Orden')
                ->numeric()
                ->default(0),
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
                TextColumn::make('questions_count')->label('Preguntas')->counts('questions'),
            ])
            ->headerActions([
                CreateAction::make(),
            ])
            ->recordActions([
                Action::make('manageQuestions')
                    ->label('Preguntas')
                    ->icon(Heroicon::OutlinedQueueList)
                    ->color('info')
                    ->url(fn ($record) => SurveySectionResource::getUrl('edit', ['record' => $record])),
                EditAction::make()->color('gray'),
                DeleteAction::make(),
            ]);
    }
}
