<?php

namespace App\Filament\Widgets;

use App\Models\Cita;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class AgendaHoyWidget extends StatsOverviewWidget
{
    protected static ?int $sort = -5;

    protected function getStats(): array
    {
        $hoy  = now()->toDateString();
        $user = auth()->user();

        // dueño ve todo; los demás ven solo su sucursal asignada
        $sucursalId = $user?->esDuenio() ? null : $user?->getSucursalId();

        $base = fn () => Cita::whereDate('fecha_hora', $hoy)
            ->when($sucursalId, fn ($q, $id) => $q->where('sucursal_id', $id));

        $total       = $base()->count();
        $pendientes  = $base()->whereIn('estado', ['pendiente', 'confirmada'])->count();
        $enProceso   = $base()->where('estado', 'en_proceso')->count();
        $completadas = $base()->where('estado', 'completada')->count();

        return [
            Stat::make('Citas hoy', $total)
                ->description('Total agendadas')
                ->color('gray')
                ->icon('heroicon-o-calendar'),

            Stat::make('En espera', $pendientes)
                ->description('Pendientes y confirmadas')
                ->color('info')
                ->icon('heroicon-o-clock'),

            Stat::make('En proceso', $enProceso)
                ->description('Atendiendo ahora')
                ->color('warning')
                ->icon('heroicon-o-scissors'),

            Stat::make('Completadas', $completadas)
                ->description('Finalizadas hoy')
                ->color('success')
                ->icon('heroicon-o-check-circle'),
        ];
    }
}
