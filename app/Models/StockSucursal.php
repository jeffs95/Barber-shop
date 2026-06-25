<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StockSucursal extends Model
{
    protected $table = 'stock_sucursal';

    protected $fillable = [
        'producto_id',
        'sucursal_id',
        'stock_actual',
        'stock_minimo',
    ];

    protected $casts = [
        'stock_actual' => 'decimal:2',
        'stock_minimo' => 'decimal:2',
    ];

    // ── Relaciones ───────────────────────────────────────────────────────

    public function producto(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Producto::class);
    }

    public function sucursal(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Sucursal::class);
    }

    // ── Helpers ──────────────────────────────────────────────────────────

    public function estaBajoMinimo(): bool
    {
        return $this->stock_actual <= $this->stock_minimo;
    }

    /**
     * Aplica un movimiento de inventario al stock de esta sucursal.
     * Devuelve el stock anterior.
     */
    public function aplicarMovimiento(string $tipo, float $cantidad): float
    {
        $antes = (float) $this->stock_actual;

        $nuevo = match ($tipo) {
            'entrada' => $antes + $cantidad,
            'salida'  => max(0, $antes - $cantidad),
            'ajuste'  => $cantidad,
        };

        $this->update(['stock_actual' => $nuevo]);

        return $antes;
    }
}
