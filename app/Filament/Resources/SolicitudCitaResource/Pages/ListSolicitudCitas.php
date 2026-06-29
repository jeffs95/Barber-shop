<?php

namespace App\Filament\Resources\SolicitudCitaResource\Pages;

use App\Filament\Resources\SolicitudCitaResource;
use Filament\Resources\Pages\ListRecords;

class ListSolicitudCitas extends ListRecords
{
    protected static string $resource = SolicitudCitaResource::class;

    // Sin acción de crear: las solicitudes solo entran desde la página pública.
    protected function getHeaderActions(): array
    {
        return [];
    }
}
