<?php

namespace App\Filament\Resources\MovimientoInventarioResource\Pages;

use App\Filament\Resources\MovimientoInventarioResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListMovimientosInventario extends ListRecords
{
    protected static string $resource = MovimientoInventarioResource::class;

    protected function getHeaderActions(): array
    {
        return [CreateAction::make()->label('Registrar movimiento')];
    }
}
