<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Caja extends Model
{
    protected $table = 'caja';

    protected $fillable = [
        'usuario_id',
        'sucursal_id',
        'fecha_apertura',
        'monto_inicial',
        'fecha_cierre',
        'monto_cierre_esperado',
        'monto_cierre_real',
        'diferencia',
        'estado',
        'notas',
    ];

    protected $casts = [
        'fecha_apertura'       => 'datetime',
        'fecha_cierre'         => 'datetime',
        'monto_inicial'        => 'float',
        'monto_cierre_esperado'=> 'float',
        'monto_cierre_real'    => 'float',
        'diferencia'           => 'float',
    ];

    // --- Relaciones ---

    public function usuario(): BelongsTo
    {
        return $this->belongsTo(Usuario::class, 'usuario_id');
    }

    public function sucursal(): BelongsTo
    {
        return $this->belongsTo(Sucursal::class, 'sucursal_id');
    }

    public function ventas(): HasMany
    {
        return $this->hasMany(Venta::class, 'caja_id');
    }

    // --- Scopes ---

    public function scopeAbierta(Builder $query): Builder
    {
        return $query->where('estado', 'abierta');
    }

    // --- Helpers ---

    public function estaAbierta(): bool
    {
        return $this->estado === 'abierta';
    }

    public static function cajaAbierta(): ?self
    {
        return static::where('estado', 'abierta')->latest()->first();
    }

    /** Caja abierta de una sucursal específica (solo puede haber una). */
    public static function cajaAbiertaDe(int $sucursalId): ?self
    {
        return static::where('estado', 'abierta')
            ->where('sucursal_id', $sucursalId)
            ->latest()
            ->first();
    }

    /** ¿Existe ya una caja abierta en esta sucursal? (opcionalmente excluye una caja). */
    public static function hayCajaAbiertaEn(int $sucursalId, ?int $exceptoId = null): bool
    {
        return static::where('estado', 'abierta')
            ->where('sucursal_id', $sucursalId)
            ->when($exceptoId, fn ($q) => $q->whereKeyNot($exceptoId))
            ->exists();
    }

    public function calcularMontoCierreEsperado(): float
    {
        $ventasEfectivo = $this->ventas()
            ->where('estado', 'completada')
            ->where('metodo_pago', 'efectivo')
            ->sum('total');

        return $this->monto_inicial + $ventasEfectivo;
    }

    public function cerrar(float $montoReal, ?string $notas = null): void
    {
        $esperado = $this->calcularMontoCierreEsperado();

        $this->update([
            'estado'                => 'cerrada',
            'fecha_cierre'          => now(),
            'monto_cierre_esperado' => $esperado,
            'monto_cierre_real'     => $montoReal,
            'diferencia'            => $montoReal - $esperado,
            'notas'                 => $notas,
        ]);
    }

    public function totalVentas(): float
    {
        return (float) $this->ventas()->where('estado', 'completada')->sum('total');
    }
}
