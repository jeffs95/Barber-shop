<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ItemVenta extends Model
{
    protected $table = 'item_venta';

    const UPDATED_AT = null;

    protected $fillable = [
        'venta_id',
        'tipo',
        'servicio_id',
        'producto_id',
        'descripcion',
        'precio_unitario',
        'cantidad',
        'subtotal',
    ];

    protected $casts = [
        'precio_unitario' => 'float',
        'cantidad'        => 'integer',
        'subtotal'        => 'float',
        'created_at'      => 'datetime',
    ];

    protected static function boot(): void
    {
        parent::boot();

        // Calcula subtotal antes de persistir
        static::creating(fn (ItemVenta $item) =>
            $item->subtotal = $item->precio_unitario * $item->cantidad
        );

        static::updating(fn (ItemVenta $item) =>
            $item->subtotal = $item->precio_unitario * $item->cantidad
        );

        // Recalcula los totales del padre cuando el ítem cambia
        static::saved(fn (ItemVenta $item) => $item->venta->recalcularTotales());
        static::deleted(fn (ItemVenta $item) => $item->venta->recalcularTotales());
    }

    // --- Relaciones ---

    public function venta(): BelongsTo
    {
        return $this->belongsTo(Venta::class, 'venta_id');
    }

    public function servicio(): BelongsTo
    {
        return $this->belongsTo(Servicio::class, 'servicio_id');
    }

    public function producto(): BelongsTo
    {
        return $this->belongsTo(Producto::class, 'producto_id');
    }
}
