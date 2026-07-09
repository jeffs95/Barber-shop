<?php

namespace Database\Seeders;

use App\Models\Rol;
use App\Models\Usuario;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // ── Datos esenciales (siempre se ejecutan) ─────────────────────────
        // Los roles son necesarios para que el panel funcione.
        $this->call(RolSeeder::class);

        // Usuario administrador inicial. Cambia el email y password antes de
        // desplegar a producción, o usa variables de entorno.
        $rolDuenio = Rol::where('nombre', 'dueño')->first();

        Usuario::firstOrCreate(
            ['email' => env('ADMIN_EMAIL', 'admin@barberia.com')],
            [
                'rol_id'   => $rolDuenio?->id,
                'nombre'   => 'Administrador',
                'apellido' => 'Barbería',
                'password' => Hash::make(env('ADMIN_PASSWORD', 'password')),
            ]
        );

        // ── Datos de demo (solo en entornos locales / de prueba) ───────────
        // En producción estos seeders NO deben correr: crean sucursales
        // ficticias, servicios de ejemplo, citas/ventas/inventario de prueba.
        if (app()->environment('local', 'testing', 'staging')) {
            $this->call([
                SucursalSeeder::class,
                CatalogoSeeder::class,
                CitaSeeder::class,
                InventarioSeeder::class,
                VentaSeeder::class,
                HorarioSeeder::class,
            ]);
        }
    }
}
