<?php

namespace App\Services;

use App\Models\Cita;
use App\Models\Empleado;
use App\Models\HorarioEmpleado;
use Illuminate\Support\Carbon;

/**
 * Calcula los huecos libres para agendar, cruzando el horario semanal de cada
 * barbero (horario_empleado) con las citas activas que ya tiene y la duración
 * del servicio solicitado.
 */
class DisponibilidadService
{
    /** Minutos entre cada hueco candidato. */
    private const PASO_MINUTOS = 15;

    /**
     * Horas de inicio disponibles ('H:i') para un barbero en una fecha dada.
     *
     * @return list<string>
     */
    public function slotsParaBarbero(int $empleadoId, Carbon $fecha, int $duracionMin): array
    {
        $duracionMin = max(5, $duracionMin);

        $horarios = HorarioEmpleado::where('empleado_id', $empleadoId)
            ->where('dia_semana', $fecha->dayOfWeekIso) // 1=lunes ... 7=domingo
            ->get();

        if ($horarios->isEmpty()) {
            return [];
        }

        $ocupadas = Cita::where('empleado_id', $empleadoId)
            ->whereIn('estado', ['pendiente', 'confirmada', 'en_proceso'])
            ->whereDate('fecha_hora', $fecha->toDateString())
            ->get(['fecha_hora', 'duracion_estimada_min'])
            ->map(fn (Cita $c) => [
                'inicio' => $c->fecha_hora,
                'fin'    => $c->fecha_hora->copy()->addMinutes($c->duracion_estimada_min),
            ]);

        $ahora = now();
        $slots = [];

        foreach ($horarios as $bloque) {
            $inicioBloque = $fecha->copy()->setTimeFromTimeString($bloque->hora_inicio);
            $finBloque    = $fecha->copy()->setTimeFromTimeString($bloque->hora_fin);

            for (
                $t = $inicioBloque->copy();
                $t->copy()->addMinutes($duracionMin)->lte($finBloque);
                $t->addMinutes(self::PASO_MINUTOS)
            ) {
                if ($t->lte($ahora)) {
                    continue; // no ofrecer horas que ya pasaron
                }

                $finSlot = $t->copy()->addMinutes($duracionMin);

                $choca = $ocupadas->contains(
                    fn (array $o) => $t->lt($o['fin']) && $finSlot->gt($o['inicio'])
                );

                if (! $choca) {
                    $slots[$t->format('H:i')] = true;
                }
            }
        }

        $horas = array_keys($slots);
        sort($horas);

        return $horas;
    }

    /**
     * Para "sin preferencia": mapa hora => barberos disponibles en la sucursal.
     *
     * @return array<string, list<int>>
     */
    public function slotsSucursal(int $sucursalId, Carbon $fecha, int $duracionMin): array
    {
        $barberos = Empleado::where('sucursal_id', $sucursalId)
            ->where('es_activo', true)
            ->where('rol', 'barbero')
            ->pluck('id');

        $mapa = [];
        foreach ($barberos as $barberoId) {
            foreach ($this->slotsParaBarbero((int) $barberoId, $fecha, $duracionMin) as $hora) {
                $mapa[$hora][] = (int) $barberoId;
            }
        }

        ksort($mapa);

        return $mapa;
    }

    /**
     * Primer barbero libre en un slot exacto (para resolver "sin preferencia" al confirmar).
     */
    public function primerBarberoDisponible(int $sucursalId, Carbon $inicio, int $duracionMin): ?int
    {
        $mapa = $this->slotsSucursal($sucursalId, $inicio, $duracionMin);

        return $mapa[$inicio->format('H:i')][0] ?? null;
    }
}
