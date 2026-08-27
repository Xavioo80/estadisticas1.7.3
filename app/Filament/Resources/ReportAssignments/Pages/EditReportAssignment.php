<?php

namespace App\Filament\Resources\ReportAssignments\Pages;

use App\Filament\Resources\ReportAssignments\ReportAssignmentResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditReportAssignment extends EditRecord
{
    protected static string $resource = ReportAssignmentResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }
}
