<?php

namespace App\Filament\Pages\Reportes;

use App\Models\Empleado;
use App\Models\Sucursal;
use Filament\Pages\Page;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Computed;

class ReporteComisiones extends Page
{
    protected string $view = 'filament.pages.reportes.comisiones';

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-currency-dollar';
    protected static ?string $navigationLabel = 'Comisiones';
    protected static string|\UnitEnum|null $navigationGroup = 'Reportes';
    protected static ?int $navigationSort = 3;
    protected static ?string $slug = 'reportes/comisiones';

    public int $mes;
    public int $anio;
    public ?int $sucursalId = null;

    public static function canAccess(): bool
    {
        return auth()->user()?->esDuenio() ?? false;
    }

    public function mount(): void
    {
        $this->mes   = (int) now()->format('m');
        $this->anio  = (int) now()->format('Y');
    }

    #[Computed]
    public function sucursales(): Collection
    {
        return Sucursal::orderBy('nombre')->get(['id', 'nombre']);
    }

    #[Computed]
    public function meses(): array
    {
        return [
            1 => 'Enero', 2 => 'Febrero', 3 => 'Marzo', 4 => 'Abril',
            5 => 'Mayo', 6 => 'Junio', 7 => 'Julio', 8 => 'Agosto',
            9 => 'Septiembre', 10 => 'Octubre', 11 => 'Noviembre', 12 => 'Diciembre',
        ];
    }

    #[Computed]
    public function anios(): array
    {
        $anioActual = (int) now()->format('Y');
        return array_combine(
            range($anioActual - 2, $anioActual),
            range($anioActual - 2, $anioActual)
        );
    }

    #[Computed]
    public function comisiones(): Collection
    {
        return Empleado::with('usuario', 'sucursal')
            ->where('es_activo', true)
            ->when($this->sucursalId, fn ($q) => $q->where('sucursal_id', $this->sucursalId))
            ->get()
            ->map(function (Empleado $empleado): array {
                // Citas completadas del período
                $citas = $empleado->citas()
                    ->where('estado', 'completada')
                    ->whereYear('fecha_hora', $this->anio)
                    ->whereMonth('fecha_hora', $this->mes)
                    ->with('servicios')
                    ->get();

                $totalServicios = $citas->sum(fn ($c) => $c->totalServicios());

                // También ventas directas en POS
                $totalVentas = DB::table('venta')
                    ->where('empleado_id', $empleado->id)
                    ->where('estado', 'completada')
                    ->whereYear('created_at', $this->anio)
                    ->whereMonth('created_at', $this->mes)
                    ->sum('total');

                $base        = $totalServicios + $totalVentas;
                $porcentaje  = (float) $empleado->porcentaje_comision;
                $comision    = round($base * $porcentaje / 100, 2);

                return [
                    'empleado'         => $empleado->nombre_completo,
                    'sucursal'         => $empleado->sucursal?->nombre ?? '—',
                    'rol'              => $empleado->rol,
                    'citas_completadas'=> $citas->count(),
                    'total_servicios'  => $totalServicios,
                    'total_ventas'     => $totalVentas,
                    'base'             => $base,
                    'porcentaje'       => $porcentaje,
                    'comision'         => $comision,
                ];
            })
            ->filter(fn ($row) => $row['citas_completadas'] > 0 || $row['total_ventas'] > 0);
    }

    #[Computed]
    public function totalComisiones(): float
    {
        return round($this->comisiones->sum('comision'), 2);
    }

    #[Computed]
    public function totalBase(): float
    {
        return round($this->comisiones->sum('base'), 2);
    }
}
