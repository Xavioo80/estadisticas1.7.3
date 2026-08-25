<?php

namespace App\Filament\Resources\ReportAssignments;

use App\Filament\Resources\ReportAssignments\Pages\CreateReportAssignment;
use App\Filament\Resources\ReportAssignments\Pages\EditReportAssignment;
use App\Filament\Resources\ReportAssignments\Pages\ListReportAssignments;
use App\Filament\Resources\ReportAssignments\Schemas\ReportAssignmentForm;
use App\Filament\Resources\ReportAssignments\Tables\ReportAssignmentsTable;
use App\Models\ReportAssignment;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class ReportAssignmentResource extends Resource
{
    protected static ?string $model = ReportAssignment::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static ?string $recordTitleAttribute = 'report_type';

    public static function form(Schema $schema): Schema
    {
        return ReportAssignmentForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return ReportAssignmentsTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListReportAssignments::route('/'),
            'create' => CreateReportAssignment::route('/create'),
            'edit' => EditReportAssignment::route('/{record}/edit'),
        ];
    }
}
