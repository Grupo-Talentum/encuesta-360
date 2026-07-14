<?php

namespace App\Filament\Admin\Resources\Employees\RelationManagers;

use App\Enums\RelationType;
use App\Models\Employee;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Select;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class RelationsRelationManager extends RelationManager
{
    protected static string $relationship = 'relations';

    protected static ?string $title = 'Relaciones adicionales';

    public function form(Schema $schema): Schema
    {
        return $schema->components([
            Select::make('related_employee_id')
                ->label('Empleado relacionado')
                ->options(fn () => Employee::query()
                    ->where('id', '!=', $this->getOwnerRecord()->id)
                    ->pluck('name', 'id'))
                ->searchable()
                ->required(),
            Select::make('type')
                ->label('Tipo de relación')
                ->options([
                    RelationType::Superior->value => 'Superior',
                    RelationType::Subordinate->value => 'Inferior',
                    RelationType::Peer->value => 'Compañero',
                ])
                ->required(),
        ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('id')
            ->modelLabel('relación adicional')
            ->pluralModelLabel('relaciones adicionales')
            ->description('El superior y los compañeros ya se arman automáticamente con el campo "Reporta a". Usá esto solo para agregar una relación extra que no sea por jerarquía (ej: un compañero de otro equipo).')
            ->columns([
                TextColumn::make('relatedEmployee.name')->label('Empleado relacionado'),
                TextColumn::make('type')->label('Tipo')->badge()->formatStateUsing(fn (RelationType $state) => match ($state) {
                    RelationType::Superior => 'Superior',
                    RelationType::Subordinate => 'Inferior',
                    RelationType::Peer => 'Compañero',
                }),
            ])
            ->headerActions([
                CreateAction::make()->label('Agregar relación'),
            ])
            ->recordActions([
                EditAction::make()->color('gray'),
                DeleteAction::make(),
            ]);
    }
}
