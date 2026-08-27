<?php

namespace App\Filament\Resources;

use App\Filament\Resources\RegistroGlobalResource\Pages;
use App\Filament\Resources\RegistroGlobalResource\RelationManagers;
use App\Models\RegistroGlobal;
use Filament\Forms;
use Filament\Schemas\Schema;
use Filament\Schemas\Components\Section;
use Filament\Actions\EditAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class RegistroGlobalResource extends Resource
{
    protected static ?string $model = RegistroGlobal::class;

    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-table-cells';
    
    protected static ?string $navigationLabel = 'Vista Nueva (ATA)';
    
    protected static ?string $modelLabel = 'Registro ATA';
    
    protected static ?string $pluralModelLabel = 'Registros ATA';

    public static function form(Schema $form): Schema
    {
        return $form
            ->schema([
                Section::make('Información General')
                    ->schema([
                        Forms\Components\TextInput::make('ano')->label('Año')->numeric(),
                        Forms\Components\TextInput::make('mes')->label('Mes'),
                        Forms\Components\DatePicker::make('fecha')->label('Fecha'),
                        Forms\Components\TextInput::make('numero')->label('N° Correlativo'),
                    ])->columns(2),
                Section::make('Paciente')
                    ->schema([
                        Forms\Components\TextInput::make('exp')->label('Expediente'),
                        Forms\Components\TextInput::make('sexo')->label('Sexo'),
                        Forms\Components\TextInput::make('edad')->label('Edad')->numeric(),
                        Forms\Components\TextInput::make('colonia')->label('Colonia'),
                    ])->columns(2),
                Section::make('Médico y Diagnóstico')
                    ->schema([
                        Forms\Components\TextInput::make('medico')->label('Médico'),
                        Forms\Components\TextInput::make('prof')->label('Profesión'),
                        Forms\Components\Textarea::make('diagnostico_1')->label('Diagnóstico Principal')->columnSpanFull(),
                    ])->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('ano')
                    ->label('Año')
                    ->sortable(),
                Tables\Columns\TextColumn::make('mes')
                    ->label('Mes')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('fecha')
                    ->label('Fecha')
                    ->date('d-m-Y')
                    ->sortable(),
                Tables\Columns\TextColumn::make('numero')
                    ->label('N°')
                    ->searchable(),
                Tables\Columns\TextColumn::make('exp')
                    ->label('Exp.')
                    ->searchable(),
                Tables\Columns\TextColumn::make('medico')
                    ->label('Médico')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('prof')
                    ->label('Prof.')
                    ->searchable(),
                Tables\Columns\TextColumn::make('sexo')
                    ->label('S'),
                Tables\Columns\TextColumn::make('edad')
                    ->label('Edad')
                    ->numeric()
                    ->sortable(),
                Tables\Columns\TextColumn::make('colonia')
                    ->label('Colonia')
                    ->searchable()
                    ->limit(20),
                Tables\Columns\TextColumn::make('diagnostico_1')
                    ->label('Diagnóstico')
                    ->searchable()
                    ->limit(30),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('ano')
                    ->label('Año')
                    ->options(function() {
                        $anos = RegistroGlobal::distinct()->orderBy('ano', 'desc')->pluck('ano', 'ano')->toArray();
                        // Filtrar nulos o vacíos para evitar el error de Filament
                        return array_filter($anos, fn($val) => !is_null($val) && $val !== '');
                    }),
                Tables\Filters\SelectFilter::make('mes')
                    ->label('Mes')
                    ->options([
                        'ENERO' => 'ENERO',
                        'FEBRERO' => 'FEBRERO',
                        'MARZO' => 'MARZO',
                        'ABRIL' => 'ABRIL',
                        'MAYO' => 'MAYO',
                        'JUNIO' => 'JUNIO',
                        'JULIO' => 'JULIO',
                        'AGOSTO' => 'AGOSTO',
                        'SEPTIEMBRE' => 'SEPTIEMBRE',
                        'OCTUBRE' => 'OCTUBRE',
                        'NOVIEMBRE' => 'NOVIEMBRE',
                        'DICIEMBRE' => 'DICIEMBRE',
                    ]),
            ])
            ->actions([
                EditAction::make(),
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
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
            'index' => Pages\ListRegistroGlobals::route('/'),
            'create' => Pages\CreateRegistroGlobal::route('/create'),
            'edit' => Pages\EditRegistroGlobal::route('/{record}/edit'),
        ];
    }
}
