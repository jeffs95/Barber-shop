<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Sucursal extends Model
{
    protected $table = 'sucursal';

    protected $fillable = [
        'nombre',
        'direccion',
        'telefono',
        'ciudad',
        'es_activa',
    ];

    protected $casts = [
        'es_activa' => 'boolean',
    ];

    // --- Relaciones ---

    public function empleados(): HasMany
    {
        return $this->hasMany(Empleado::class, 'sucursal_id');
    }

    public function citas(): HasMany
    {
        return $this->hasMany(Cita::class, 'sucursal_id');
    }

    public function cajas(): HasMany
    {
        return $this->hasMany(Caja::class, 'sucursal_id');
    }

    public function movimientos(): HasMany
    {
        return $this->hasMany(MovimientoInventario::class, 'sucursal_id');
    }

    // --- Helpers ---

    public function cajaAbierta(): ?Caja
    {
        return $this->cajas()->where('estado', 'abierta')->latest()->first();
    }

    public function totalVentasHoy(): float
    {
        return (float) Venta::whereHas('caja', fn ($q) => $q->where('sucursal_id', $this->id))
            ->where('estado', 'completada')
            ->whereDate('created_at', today())
            ->sum('total');
    }

    public function citasHoy(): int
    {
        return $this->citas()
            ->whereDate('fecha_hora', today())
            ->whereNotIn('estado', ['cancelada', 'no_asistio'])
            ->count();
    }
}
