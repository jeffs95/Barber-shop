<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Venta extends Model
{
    protected $table = 'venta';

    protected $fillable = [
        'caja_id',
        'empleado_id',
        'cliente_id',
        'cita_id',
        'subtotal',
        'descuento',
        'propina',
        'total',
        'metodo_pago',
        'estado',
        'notas',
    ];

    protected $casts = [
        'subtotal'  => 'float',
        'descuento' => 'float',
        'propina'   => 'float',
        'total'     => 'float',
    ];

    // --- Relaciones ---

    public function caja(): BelongsTo
    {
        return $this->belongsTo(Caja::class, 'caja_id');
    }

    public function empleado(): BelongsTo
    {
        return $this->belongsTo(Empleado::class, 'empleado_id');
    }

    public function cliente(): BelongsTo
    {
        return $this->belongsTo(Cliente::class, 'cliente_id');
    }

    public function cita(): BelongsTo
    {
        return $this->belongsTo(Cita::class, 'cita_id');
    }

    public function items(): HasMany
    {
        return $this->hasMany(ItemVenta::class, 'venta_id');
    }

    // --- Helpers ---

    public function recalcularTotales(): void
    {
        $subtotal = $this->items()->sum('subtotal');

        $this->update([
            'subtotal' => $subtotal,
            'total'    => max(0, $subtotal - $this->descuento + $this->propina),
        ]);
    }

    public static function metodoPagoLabel(string $metodo): string
    {
        return match($metodo) {
            'efectivo'      => 'Efectivo',
            'tarjeta'       => 'Tarjeta',
            'transferencia' => 'Transferencia',
            default         => 'Otro',
        };
    }

    public static function estadoColor(string $estado): string
    {
        return match($estado) {
            'completada'  => 'success',
            'cancelada'   => 'danger',
            'reembolsada' => 'warning',
        };
    }
}
