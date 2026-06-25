<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MovimientoInventario extends Model
{
    protected $table = 'movimiento_inventario';

    // Solo created_at, no updated_at
    const UPDATED_AT = null;

    protected $fillable = [
        'producto_id',
        'usuario_id',
        'sucursal_id',
        'tipo',
        'cantidad',
        'stock_antes',
        'stock_despues',
        'motivo',
        'referencia',
    ];

    protected $casts = [
        'cantidad'      => 'integer',
        'stock_antes'   => 'integer',
        'stock_despues' => 'integer',
        'created_at'    => 'datetime',
    ];

    protected static function boot(): void
    {
        parent::boot();

        static::creating(function (MovimientoInventario $mov) {
            $producto = Producto::find($mov->producto_id);

            if ($mov->sucursal_id) {
                // ── Stock por sucursal ────────────────────────────────────
                $stockReg = $producto->stockSucursal($mov->sucursal_id);

                $mov->stock_antes = (float) $stockReg->stock_actual;

                $stockReg->aplicarMovimiento($mov->tipo, $mov->cantidad);

                $mov->stock_despues = (float) $stockReg->fresh()->stock_actual;

                // Recalcular total global
                $producto->recalcularStockGlobal();
            } else {
                // ── Stock global (sin sucursal asignada) ─────────────────
                $mov->stock_antes = $producto->stock_actual;

                $nuevo = match ($mov->tipo) {
                    'entrada' => $producto->stock_actual + $mov->cantidad,
                    'salida'  => max(0, $producto->stock_actual - $mov->cantidad),
                    'ajuste'  => $mov->cantidad,
                };

                $mov->stock_despues = $nuevo;

                Producto::where('id', $mov->producto_id)->update(['stock_actual' => $nuevo]);
            }
        });
    }

    // --- Relaciones ---

    public function producto(): BelongsTo
    {
        return $this->belongsTo(Producto::class, 'producto_id');
    }

    public function usuario(): BelongsTo
    {
        return $this->belongsTo(Usuario::class, 'usuario_id');
    }

    public function sucursal(): BelongsTo
    {
        return $this->belongsTo(Sucursal::class, 'sucursal_id');
    }

    // --- Helpers ---

    public static function tipoLabel(string $tipo): string
    {
        return match($tipo) {
            'entrada' => 'Entrada',
            'salida'  => 'Salida',
            'ajuste'  => 'Ajuste',
        };
    }

    public static function tipoColor(string $tipo): string
    {
        return match($tipo) {
            'entrada' => 'success',
            'salida'  => 'danger',
            'ajuste'  => 'warning',
        };
    }
}
