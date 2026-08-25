<?php

namespace App\Filament\Resources\ReportAssignments\Schemas;

use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Hidden;
use Filament\Schemas\Schema;
use Illuminate\Support\Facades\Auth;

class ReportAssignmentForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('user_id')
                    ->label('Usuario (Médico)')
                    ->relationship('user', 'name', fn ($query) => $query->where('role', 'medico'))
                    ->searchable()
                    ->required(),
                
                Select::make('report_type')
                    ->label('Tipo de Informe')
                    ->options([
                        'AT1' => 'AT1 - Consulta Externa',
                        'AT2' => 'AT2 - Consolidado Mensual',
                        'AT2rN' => 'AT2r-N - Nutrición',
                        'SM107' => 'SM1-07 - Salud Mental',
                        'SM307' => 'SM3-07 - Salud Mental Detalle',
                    ])
                    ->required(),
                
                DatePicker::make('due_date')
                    ->label('Fecha Límite')
                    ->default(now()->endOfMonth())
                    ->required(),
                
                Select::make('status')
                    ->label('Estado')
                    ->options([
                        'pending' => 'Pendiente',
                        'completed' => 'Completado',
                        'overdue' => 'Atrasado',
                    ])
                    ->default('pending')
                    ->required(),
                
                Hidden::make('assigned_by')
                    ->default(Auth::id()),
            ]);
    }
}
