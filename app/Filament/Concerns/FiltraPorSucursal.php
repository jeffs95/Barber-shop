<?php

namespace App\Filament\Concerns;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\SoftDeletingScope;

/**
 * Aplica a resources de Filament que tienen columna sucursal_id.
 * Si el usuario logueado es admin_sucursal, restringe automáticamente
 * el query a los registros de su sucursal asignada.
 */
trait FiltraPorSucursal
{
    public static function getEloquentQuery(): Builder
    {
        return static::aplicarScopesSucursal(parent::getEloquentQuery());
    }

    /**
     * Oculta los soft-deletes (si aplica) y restringe a la sucursal del admin.
     * Expuesto aparte para que un resource con su propio getEloquentQuery
     * (p.ej. CitaResource y SolicitudCitaResource) pueda reutilizarlo.
     */
    protected static function aplicarScopesSucursal(Builder $query): Builder
    {
        // Si el modelo usa SoftDeletes, incluimos los eliminados para que el
        // TrashedFilter de la tabla pueda mostrarlos u ocultarlos a voluntad.
        if (in_array(SoftDeletes::class, class_uses_recursive(static::getModel()), true)) {
            $query->withoutGlobalScopes([SoftDeletingScope::class]);
        }

        $user = auth()->user();
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
