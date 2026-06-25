<?php

namespace App\Filament\Resources\CitaResource\Pages;

use App\Filament\Resources\CitaResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListCitas extends ListRecords
{
    protected static string $resource = CitaResource::class;

    protected function getHeaderActions(): array
    {
        return [CreateAction::make()->label('Nueva cita')];
    }
}
