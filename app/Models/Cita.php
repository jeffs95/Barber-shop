<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Cita extends Model
{
    use HasFactory;

    protected $table = 'cita';

    protected $fillable = [
        'cliente_id',
        'empleado_id',
        'sucursal_id',
        'fecha_hora',
        'duracion_estimada_min',
        'estado',
        'origen',
        'notas',
        'notas_barbero',
    ];

    protected $casts = [
        'fecha_hora'           => 'datetime',
        'duracion_estimada_min' => 'integer',
    ];

    // ─── Relaciones ─────────────────────────────────────────────────────────

    public function cliente(): BelongsTo
    {
        return $this->belongsTo(Cliente::class, 'cliente_id');
    }

    public function empleado(): BelongsTo
    {
        return $this->belongsTo(Empleado::class, 'empleado_id');
    }

    public function sucursal(): BelongsTo
    {
        return $this->belongsTo(Sucursal::class, 'sucursal_id');
    }

    public function servicios(): BelongsToMany
    {
        return $this->belongsToMany(Servicio::class, 'cita_servicio', 'cita_id', 'servicio_id')
            ->withPivot('precio');
    }

    public function itemsCita(): HasMany
    {
        return $this->hasMany(CitaServicio::class, 'cita_id');
    }

    // ─── Helpers ─────────────────────────────────────────────────────────────

    public function getHoraFinAttribute(): \Illuminate\Support\Carbon
    {
        return $this->fecha_hora->copy()->addMinutes($this->duracion_estimada_min);
    }

    public function estaActiva(): bool
    {
        return in_array($this->estado, ['pendiente', 'confirmada', 'en_proceso']);
    }

    public function totalServicios(): float
    {
        return (float) $this->servicios->sum('pivot.precio');
    }

    public function recalcularDuracion(): void
    {
        $this->duracion_estimada_min = $this->servicios->sum('duracion_minutos') ?: 30;
        $this->saveQuietly();
    }

    // ─── Etiquetas y colores ──────────────────────────────────────────────────

    public static function estadoColor(string $estado): string
    {
        return match ($estado) {
            'pendiente'   => 'gray',
            'confirmada'  => 'info',
            'en_proceso'  => 'warning',
            'completada'  => 'success',
            'cancelada'   => 'danger',
            'no_asistio'  => 'warning',
            default       => 'gray',
        };
    }

    public static function estadoLabel(string $estado): string
    {
        return match ($estado) {
            'pendiente'   => 'Pendiente',
            'confirmada'  => 'Confirmada',
            'en_proceso'  => 'En proceso',
            'completada'  => 'Completada',
            'cancelada'   => 'Cancelada',
            'no_asistio'  => 'No asistió',
            default       => $estado,
        };
    }

    public static function estadoOpciones(): array
    {
        return [
            'pendiente'   => 'Pendiente',
            'confirmada'  => 'Confirmada',
            'en_proceso'  => 'En proceso',
            'completada'  => 'Completada',
            'cancelada'   => 'Cancelada',
            'no_asistio'  => 'No asistió',
        ];
    }
}
