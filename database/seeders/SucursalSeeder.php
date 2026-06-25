<?php

namespace Database\Seeders;

use App\Models\Sucursal;
use Illuminate\Database\Seeder;

class SucursalSeeder extends Seeder
{
    public function run(): void
    {
        Sucursal::create([
            'nombre'    => 'Sucursal Centro',
            'direccion' => '5ª Avenida 10-15, Zona 1',
            'telefono'  => '2234-5678',
            'ciudad'    => 'Guatemala',
            'es_activa' => true,
        ]);

        Sucursal::create([
            'nombre'    => 'Sucursal Norte',
            'direccion' => '18 Calle 25-30, Zona 10',
            'telefono'  => '2290-1234',
            'ciudad'    => 'Guatemala',
            'es_activa' => true,
        ]);
    }
}
