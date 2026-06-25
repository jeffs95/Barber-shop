<?php

namespace App\Filament\Concerns;

use Illuminate\Database\Eloquent\Builder;

/**
 * Aplica a resources de Filament que tienen columna sucursal_id.
 * Si el usuario logueado es admin_sucursal, restringe automáticamente
 * el query a los registros de su sucursal asignada.
 */
trait FiltraPorSucursal
{
    public static function getEloquentQuery(): Builder
    {
        $query = parent::getEloquentQuery();
        $user  = auth()->user();

        if ($user?->esAdminSucursal() && ($sucursalId = $user->getSucursalId())) {
            $query->where(static::getTable() . '.sucursal_id', $sucursalId);
        }

        return $query;
    }

    /** Devuelve el nombre de la tabla del modelo del resource. */
    protected static function getTable(): string
    {
        return (new (static::getModel()))->getTable();
    }
}
