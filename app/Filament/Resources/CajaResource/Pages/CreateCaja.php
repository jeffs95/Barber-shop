<?php

namespace App\Filament\Resources\CajaResource\Pages;

use App\Filament\Resources\CajaResource;
use Filament\Resources\Pages\CreateRecord;

class CreateCaja extends CreateRecord
{
    protected static string $resource = CajaResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['usuario_id'] ??= auth()->id();
        $data['fecha_apertura'] ??= now();
        $data['estado'] = 'abierta';
        return $data;
    }
}
