<?php

namespace App\Filament\Resources\ServicioResource\Pages;

use App\Filament\Resources\ServicioResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListServicios extends ListRecords
{
    protected static string $resource = ServicioResource::class;

    protected function getHeaderActions(): array
    {
        return [CreateAction::make()];
    }
}
