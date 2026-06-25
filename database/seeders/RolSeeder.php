<?php

namespace Database\Seeders;

use App\Models\Rol;
use Illuminate\Database\Seeder;

class RolSeeder extends Seeder
{
    public function run(): void
    {
        $roles = [
            [
                'nombre'      => 'dueño',
                'descripcion' => 'Propietario del negocio. Acceso total a todas las sucursales y configuración.',
            ],
            [
                'nombre'      => 'admin_sucursal',
                'descripcion' => 'Administrador de una sucursal. Gestiona citas, caja, ventas y empleados de su sucursal.',
            ],
            [
                'nombre'      => 'barbero',
                'descripcion' => 'Empleado que realiza servicios. Acceso limitado a su agenda.',
            ],
            [
                'nombre'      => 'recepcionista',
                'descripcion' => 'Gestiona citas y atención al cliente en su sucursal.',
            ],
        ];

        foreach ($roles as $rol) {
            Rol::firstOrCreate(['nombre' => $rol['nombre']], $rol);
        }
    }
}
