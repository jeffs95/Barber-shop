<?php

namespace Database\Seeders;

use App\Models\Cita;
use App\Models\CitaServicio;
use App\Models\Cliente;
use App\Models\Empleado;
use App\Models\Servicio;
use App\Models\Sucursal;
use App\Models\Usuario;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class CitaSeeder extends Seeder
{
    public function run(): void
    {
        // Barbero de prueba
        $usuarioBarbero = Usuario::firstOrCreate(
            ['email' => 'barbero@barberia.com'],
            [
                'nombre'            => 'Carlos',
                'apellido'          => 'Pérez',
                'email_verified_at' => now(),
                'password'          => Hash::make('password'),
            ]
        );

        $sucursal = Sucursal::first();

        $barbero = Empleado::firstOrCreate(
            ['usuario_id' => $usuarioBarbero->id],
            [
                'sucursal_id'          => $sucursal?->id,
                'rol'                  => 'barbero',
                'porcentaje_comision'  => 40,
                'color_agenda'         => '#F59E0B',
                'es_activo'            => true,
            ]
        );

        // Clientes de prueba
        $clientes = collect([
            ['nombre' => 'Juan',    'apellido' => 'García',   'telefono' => '5001-1001'],
            ['nombre' => 'Miguel',  'apellido' => 'López',    'telefono' => '5001-1002'],
            ['nombre' => 'Roberto', 'apellido' => 'Méndez',   'telefono' => '5001-1003'],
            ['nombre' => 'Diego',   'apellido' => 'Cifuentes','telefono' => '5001-1004'],
        ])->map(fn ($d) => Cliente::firstOrCreate(['telefono' => $d['telefono']], array_merge($d, ['tipo' => 'regular'])));

        $corte  = Servicio::where('nombre', 'Corte Clásico')->first();
        $barba  = Servicio::where('nombre', 'Perfilado de Barba')->first();
        $fade   = Servicio::where('nombre', 'Corte Moderno / Fade')->first();
        $navaja = Servicio::where('nombre', 'Afeitado Clásico con Navaja')->first();

        $hoy = now();
        $sucursalId = $sucursal?->id;

        $citas = [
            // Hoy — diferentes estados
            [
                'cliente_id'          => $clientes[0]->id,
                'empleado_id'         => $barbero->id,
                'sucursal_id'         => $sucursalId,
                'fecha_hora'          => $hoy->copy()->setTime(9, 0),
                'duracion_estimada_min' => 30,
                'estado'              => 'completada',
                'origen'              => 'presencial',
                'servicios'           => [[$corte, $corte->precio_base]],
            ],
            [
                'cliente_id'          => $clientes[1]->id,
                'empleado_id'         => $barbero->id,
                'sucursal_id'         => $sucursalId,
                'fecha_hora'          => $hoy->copy()->setTime(10, 0),
                'duracion_estimada_min' => 50,
                'estado'              => 'completada',
                'origen'              => 'presencial',
                'servicios'           => [[$corte, $corte->precio_base], [$barba, $barba->precio_base]],
            ],
            [
                'cliente_id'          => $clientes[2]->id,
                'empleado_id'         => $barbero->id,
                'sucursal_id'         => $sucursalId,
                'fecha_hora'          => $hoy->copy()->setTime(11, 30),
                'duracion_estimada_min' => 45,
                'estado'              => 'en_proceso',
                'origen'              => 'telefono',
                'servicios'           => [[$fade, $fade->precio_base]],
            ],
            [
                'cliente_id'          => $clientes[3]->id,
                'empleado_id'         => $barbero->id,
                'sucursal_id'         => $sucursalId,
                'fecha_hora'          => $hoy->copy()->setTime(13, 0),
                'duracion_estimada_min' => 30,
                'estado'              => 'confirmada',
                'origen'              => 'enlace',
                'servicios'           => [[$corte, $corte->precio_base]],
            ],
            [
                'cliente_id'          => null,
                'empleado_id'         => $barbero->id,
                'sucursal_id'         => $sucursalId,
                'fecha_hora'          => $hoy->copy()->setTime(14, 0),
                'duracion_estimada_min' => 30,
                'estado'              => 'pendiente',
                'origen'              => 'presencial',
                'notas'               => 'Walk-in, espera en sala',
                'servicios'           => [[$corte, $corte->precio_base]],
            ],
            // Mañana
            [
                'cliente_id'          => $clientes[0]->id,
                'empleado_id'         => $barbero->id,
                'sucursal_id'         => $sucursalId,
                'fecha_hora'          => $hoy->copy()->addDay()->setTime(10, 0),
                'duracion_estimada_min' => 75,
                'estado'              => 'pendiente',
                'origen'              => 'enlace',
                'servicios'           => [[$fade, $fade->precio_base], [$navaja, $navaja->precio_base]],
            ],
        ];

        foreach ($citas as $data) {
            $serviciosDatos = $data['servicios'];
            unset($data['servicios']);

            $cita = Cita::create($data);

            foreach ($serviciosDatos as [$servicio, $precio]) {
                CitaServicio::create([
                    'cita_id'     => $cita->id,
                    'servicio_id' => $servicio->id,
                    'precio'      => $precio,
                ]);
            }
        }
    }
}
