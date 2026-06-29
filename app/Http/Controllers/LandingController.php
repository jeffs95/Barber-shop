<?php

namespace App\Http\Controllers;

use App\Models\Empleado;
use App\Models\Servicio;
use App\Models\Sucursal;

class LandingController extends Controller
{
    /**
     * Página pública de la barbería: catálogo de servicios, equipo, sucursales
     * y el asistente de reservas embebido.
     */
    public function __invoke()
    {
        $servicios = Servicio::where('es_activo', true)
            ->with('categoria')
            ->orderBy('nombre')
            ->get()
            ->groupBy(fn (Servicio $s) => $s->categoria?->nombre ?? 'Otros servicios');

        $barberos = Empleado::where('es_activo', true)
            ->where('rol', 'barbero')
            ->with('usuario')
            ->get();

        $sucursales = Sucursal::where('es_activa', true)
            ->orderBy('nombre')
            ->get();

        return view('landing', compact('servicios', 'barberos', 'sucursales'));
    }
}
