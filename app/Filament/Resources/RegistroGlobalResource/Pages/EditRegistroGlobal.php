<?php

namespace App\Filament\Resources\RegistroGlobalResource\Pages;

use App\Filament\Resources\RegistroGlobalResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditRegistroGlobal extends EditRecord
{
    protected static string $resource = RegistroGlobalResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\DeleteAction::make(),
        ];
    }
}
