<?php

namespace App\Filament\Admin\Resources\NpsSurveys\Pages;

use App\Filament\Admin\Resources\NpsSurveys\NpsSurveyResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListNpsSurveys extends ListRecords
{
    protected static string $resource = NpsSurveyResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
