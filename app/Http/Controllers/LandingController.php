<?php

namespace App\Http\Controllers;

use App\Models\Empleado;
use App\Models\Producto;
use App\Models\Servicio;
use App\Models\Sucursal;

class LandingController extends Controller
{
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

        $productos = Producto::where('es_activo', true)
            ->orderBy('stock_actual', 'desc')
            ->orderBy('nombre')
            ->get()
            ->groupBy(fn (Producto $p) => Producto::categoriaLabel($p->categoria));

        return view('landing', compact('servicios', 'barberos', 'sucursales', 'productos'));
    }
}
