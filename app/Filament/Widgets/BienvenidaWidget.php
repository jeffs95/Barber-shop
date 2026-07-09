<?php

namespace App\Filament\Widgets;

use App\Models\Cita;
use Filament\Widgets\Widget;
use Livewire\Attributes\Computed;

class BienvenidaWidget extends Widget
{
    protected string $view = 'filament.widgets.bienvenida-widget';

    protected static ?int $sort = -10;

    protected int|string|array $columnSpan = 'full';

    #[Computed]
    public function citasActivasHoy(): int
    {
        $user       = auth()->user();
        $sucursalId = $user?->esDuenio() ? null : $user?->getSucursalId();

        return Cita::whereDate('fecha_hora', now()->toDateString())
            ->when($sucursalId, fn ($q, $id) => $q->where('sucursal_id', $id))
            ->whereIn('estado', ['pendiente', 'confirmada', 'en_proceso'])
            ->count();
    }

    #[Computed]
    public function fechaTexto(): string
    {
        $dias   = ['Domingo', 'Lunes', 'Martes', 'Miércoles', 'Jueves', 'Viernes', 'Sábado'];
        $meses  = ['enero', 'febrero', 'marzo', 'abril', 'mayo', 'junio',
                   'julio', 'agosto', 'septiembre', 'octubre', 'noviembre', 'diciembre'];
        $hoy    = now();

        return $dias[$hoy->dayOfWeek] . ', ' . $hoy->day . ' de ' . $meses[$hoy->month - 1] . ' de ' . $hoy->year;
    }

    #[Computed]
    public function saludo(): string
    {
        $hora = now()->hour;

        return match (true) {
            $hora < 12 => 'Buenos días',
            $hora < 19 => 'Buenas tardes',
            default    => 'Buenas noches',
        };
    }
}
