<?php

namespace App\Filament\Resources\VentaResource\Pages;

use App\Filament\Resources\VentaResource;
use Filament\Resources\Pages\CreateRecord;

class CreateVenta extends CreateRecord
{
    protected static string $resource = VentaResource::class;

    protected function afterCreate(): void
    {
        $this->record->recalcularTotales();
    }
}
