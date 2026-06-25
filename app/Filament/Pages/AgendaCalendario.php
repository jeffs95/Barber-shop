<?php

namespace App\Filament\Pages;

use App\Filament\Resources\CitaResource;
use App\Models\Cita;
use App\Models\Empleado;
use Filament\Pages\Page;
use Illuminate\Support\Collection;
use Livewire\Attributes\Computed;

class AgendaCalendario extends Page
{
    protected string $view = 'filament.pages.agenda-calendario';

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-calendar';
    protected static ?string $navigationLabel = 'Calendario';
    protected static string|\UnitEnum|null $navigationGroup = 'Agenda';
    protected static ?int $navigationSort = 0;
    protected static ?string $slug = 'agenda';
    protected static ?string $title = 'Agenda';

    public ?int $empleadoFiltro = null;

    public static function canAccess(): bool
    {
        $user = auth()->user();
        return $user?->esDuenio() || $user?->esAdminSucursal();
    }

    // Cuando cambia el filtro, avisa al calendario para refrescar
    public function updatedEmpleadoFiltro(): void
    {
        $this->dispatch('citas-refetch');
    }

    #[Computed]
    public function empleados(): Collection
    {
        return Empleado::with('usuario')
            ->where('es_activo', true)
            ->where('rol', 'barbero')
            ->orderBy('id')
            ->get();
    }

    // Llamado desde FullCalendar (via $wire) para obtener los eventos del rango visible
    public function getCitasParaCalendario(string $inicio, string $fin): array
    {
        $user = auth()->user();

        return Cita::with(['cliente', 'empleado.usuario'])
            ->where('fecha_hora', '>=', $inicio)
            ->where('fecha_hora', '<=', $fin)
            ->when($this->empleadoFiltro, fn ($q) => $q->where('empleado_id', $this->empleadoFiltro))
            ->when(
                $user?->esAdminSucursal() && $user->getSucursalId(),
                fn ($q) => $q->where('sucursal_id', $user->getSucursalId())
            )
            ->get()
            ->map(fn (Cita $c) => [
                'id'              => $c->id,
                'title'           => $c->cliente?->nombreCompleto() ?? 'Walk-in',
                'start'           => $c->fecha_hora->toIso8601String(),
                'end'             => $c->hora_fin->toIso8601String(),
                'backgroundColor' => $c->empleado?->color_agenda ?? '#f59e0b',
                'borderColor'     => $c->empleado?->color_agenda ?? '#d97706',
                'textColor'       => '#ffffff',
                'extendedProps'   => [
                    'estado'      => $c->estado,
                    'estadoLabel' => Cita::estadoLabel($c->estado),
                    'estadoColor' => Cita::estadoColor($c->estado),
                    'barbero'     => $c->empleado?->nombre_completo ?? '—',
                    'cliente'     => $c->cliente?->nombreCompleto() ?? 'Walk-in',
                    'duracion'    => $c->duracion_estimada_min,
                ],
            ])
            ->values()
            ->toArray();
    }

    // Drag & drop: mueve una cita a una nueva fecha/hora
    public function moverCita(int $citaId, string $nuevaFechaHora): bool
    {
        $cita = Cita::find($citaId);
        if (! $cita || ! $cita->estaActiva()) {
            return false;
        }

        $nueva = \Carbon\Carbon::parse($nuevaFechaHora);
        $cita->update(['fecha_hora' => $nueva]);

        return true;
    }

    public function getCreateUrl(): string
    {
        return CitaResource::getUrl('create');
    }

    public function getEditUrlBase(): string
    {
        // Devuelve p.ej. "http://localhost:8000/admin/citas"
        return rtrim(CitaResource::getUrl('index'), '/');
    }
}
