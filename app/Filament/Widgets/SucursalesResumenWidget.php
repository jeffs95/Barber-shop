<?php

namespace App\Filament\Widgets;

use App\Models\Sucursal;
use Filament\Widgets\Widget;
use Livewire\Attributes\Computed;

class SucursalesResumenWidget extends Widget
{
    protected string $view = 'filament.widgets.sucursales-resumen-widget';

    protected static ?int $sort = -3;

    protected int|string|array $columnSpan = 'full';

    #[Computed]
    public function sucursales(): \Illuminate\Support\Collection
    {
        $user = auth()->user();

        // dueño ve todas las sucursales; el resto ve solo la que tiene asignada
        $query = Sucursal::where('es_activa', true)->orderBy('nombre');

        if (! $user?->esDuenio() && ($sucursalId = $user?->getSucursalId())) {
            $query->where('id', $sucursalId);
        }

        return $query->get()
            ->map(fn (Sucursal $s) => [
                'id'           => $s->id,
                'nombre'       => $s->nombre,
                'ciudad'       => $s->ciudad ?? '',
                'direccion'    => $s->direccion ?? '',
                'telefono'     => $s->telefono ?? '',
                'caja_abierta' => $s->cajaAbierta() !== null,
                'ventas_hoy'   => $s->totalVentasHoy(),
                'citas_hoy'    => $s->citasHoy(),
                'empleados'    => $s->empleados()->where('es_activo', true)->count(),
            ]);
    }
}
