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

        // El admin de sucursal siempre abre caja en SU sede, pase lo que pase en el form.
        $user = auth()->user();
        if ($user?->esAdminSucursal() && $user->getSucursalId()) {
            $data['sucursal_id'] = $user->getSucursalId();
        }

        return $data;
    }
}
