<?php

namespace App\Filament\Admin\Resources\Employees;

use App\Filament\Admin\Resources\Employees\Pages\CreateEmployee;
use App\Filament\Admin\Resources\Employees\Pages\EditEmployee;
use App\Filament\Admin\Resources\Employees\Pages\ListEmployees;
use App\Filament\Admin\Resources\Employees\RelationManagers\RelationsRelationManager;
use App\Models\Employee;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class EmployeeResource extends Resource
{
    protected static ?string $model = Employee::class;

    protected static string|\BackedEnum|null $navigationIcon = Heroicon::OutlinedUsers;

    protected static ?string $navigationLabel = 'Participantes';

    protected static ?string $modelLabel = 'participante';

    protected static ?string $pluralModelLabel = 'participantes';

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            TextInput::make('name')
                ->label('Nombre')
                ->required()
                ->maxLength(255),
            TextInput::make('email')
                ->label('Email')
                ->email()
                ->required()
                ->unique(ignoreRecord: true)
                ->maxLength(255),
            Select::make('team_id')
                ->label('Equipo')
                ->relationship('team', 'name')
                ->searchable()
                ->preload()
                ->live()
                ->afterStateUpdated(fn ($set) => $set('superior_id', null)),
            Select::make('superior_id')
                ->label('Reporta a')
                ->options(fn (Get $get, ?Employee $record) => Employee::query()
                    ->when($get('team_id'), fn ($query, $teamId) => $query->where('team_id', $teamId))
                    ->when($record, fn ($query) => $query->where('id', '!=', $record->id))
                    ->pluck('name', 'id'))
                ->searchable()
                ->preload()
                ->helperText('Solo se muestran participantes del mismo equipo. Dejar vacío si esta persona no reporta a nadie (tope de la jerarquía). Los compañeros que reportan al mismo superior se relacionan automáticamente entre sí.'),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')->label('Nombre')->searchable(),
                TextColumn::make('email')->label('Email')->searchable(),
                TextColumn::make('team.name')->label('Equipo')->sortable(),
                TextColumn::make('reportsTo.name')->label('Reporta a')->placeholder('—'),
            ])
            ->defaultSort('name');
    }

    public static function getRelations(): array
    {
        return [
            RelationsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListEmployees::route('/'),
            'create' => CreateEmployee::route('/create'),
            'edit' => EditEmployee::route('/{record}/edit'),
        ];
    }
}
