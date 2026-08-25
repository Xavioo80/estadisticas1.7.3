<?php

namespace App\Filament\Resources\ReportAssignments\Pages;

use App\Filament\Resources\ReportAssignments\ReportAssignmentResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListReportAssignments extends ListRecords
{
    protected static string $resource = ReportAssignmentResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}
