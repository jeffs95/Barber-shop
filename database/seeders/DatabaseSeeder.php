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
        $this->call(RolSeeder::class);

        $rolDuenio = Rol::where('nombre', 'dueño')->first();

        Usuario::factory()->create([
            'rol_id'   => $rolDuenio?->id,
            'nombre'   => 'Administrador',
            'apellido' => 'Barbería',
            'email'    => 'admin@barberia.com',
            'password' => Hash::make('password'),
        ]);

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
