<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Producto extends Model
{
    protected $table = 'producto';

    protected $fillable = [
        'proveedor_id',
        'nombre',
        'descripcion',
        'codigo_barras',
        'precio_compra',
        'precio_venta',
        'stock_actual',
        'stock_minimo',
        'unidad',
        'categoria',
        'es_activo',
    ];

    protected $casts = [
        'precio_compra' => 'float',
        'precio_venta'  => 'float',
        'stock_actual'  => 'integer',
        'stock_minimo'  => 'integer',
        'es_activo'     => 'boolean',
    ];

    // --- Relaciones ---

    public function proveedor(): BelongsTo
    {
        return $this->belongsTo(Proveedor::class, 'proveedor_id');
    }

    public function movimientos(): HasMany
    {
        return $this->hasMany(MovimientoInventario::class, 'producto_id');
    }

    public function stockSucursales(): HasMany
    {
        return $this->hasMany(StockSucursal::class, 'producto_id');
    }

    // --- Scopes ---

    public function scopeActivo(Builder $query): Builder
    {
        return $query->where('es_activo', true);
    }

    public function scopeStockBajo(Builder $query): Builder
    {
        return $query->whereColumn('stock_actual', '<=', 'stock_minimo');
    }

    // --- Helpers de stock por sucursal ---

    public function stockEnSucursal(int $sucursalId): float
    {
        return (float) ($this->stockSucursales->firstWhere('sucursal_id', $sucursalId)?->stock_actual ?? 0);
    }

    /**
     * Obtiene o crea el registro de stock para una sucursal dada.
     */
    public function stockSucursal(int $sucursalId): StockSucursal
    {
        return StockSucursal::firstOrCreate(
            ['producto_id' => $this->id, 'sucursal_id' => $sucursalId],
            ['stock_actual' => 0, 'stock_minimo' => $this->stock_minimo]
        );
    }

    /**
     * Recalcula stock_actual global sumando todos los stocks de sucursales.
     */
    public function recalcularStockGlobal(): void
    {
        $total = (int) $this->stockSucursales()->sum('stock_actual');
        $this->update(['stock_actual' => $total]);
    }

    // --- Helpers ---

    public function getEstadoStockAttribute(): string
    {
        if ($this->stock_actual <= 0) {
            return 'sin_stock';
        }
        if ($this->stock_actual <= $this->stock_minimo) {
            return 'bajo';
        }
        return 'normal';
    }

    public function getMargenAttribute(): float
    {
        if ($this->precio_compra == 0) {
            return 0;
        }
        return round((($this->precio_venta - $this->precio_compra) / $this->precio_compra) * 100, 1);
    }

    public static function categoriaLabel(string $categoria): string
    {
        return match($categoria) {
            'cuidado_cabello' => 'Cuidado del cabello',
            'barba'           => 'Barba',
            'herramienta'     => 'Herramienta',
            'consumible'      => 'Consumible',
            default           => 'Otro',
        };
    }
}
