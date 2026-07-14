<?php

namespace App\Filament\Admin\Resources\SurveySections\Pages;

use App\Filament\Admin\Resources\SurveySections\SurveySectionResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditSurveySection extends EditRecord
{
    protected static string $resource = SurveySectionResource::class;

    public function hasResourceBreadcrumbs(): bool
    {
        return false;
    }

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
