<?php

namespace Database\Seeders;

use App\Models\Empleado;
use App\Models\HorarioEmpleado;
use Illuminate\Database\Seeder;

class HorarioSeeder extends Seeder
{
    /**
     * Horario semanal de ejemplo para cada barbero: lunes a sábado, 9:00–18:00.
     * Sin estos horarios, la página pública de reservas no muestra huecos disponibles.
     */
    public function run(): void
    {
        $barberos = Empleado::where('rol', 'barbero')->get();

        foreach ($barberos as $barbero) {
            // 1 = lunes ... 6 = sábado (domingo descansa).
            foreach (range(1, 6) as $dia) {
                HorarioEmpleado::updateOrCreate(
                    ['empleado_id' => $barbero->id, 'dia_semana' => $dia],
                    ['hora_inicio' => '09:00', 'hora_fin' => '18:00'],
                );
            }
        }
    }
}
