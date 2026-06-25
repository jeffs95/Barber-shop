<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Combo extends Model
{
    use HasFactory;

    protected $table = 'combo';

    protected $fillable = [
        'nombre',
        'descripcion',
        'precio',
        'porcentaje_descuento',
        'fecha_inicio',
        'fecha_fin',
        'es_activo',
    ];

    protected $casts = [
        'precio'               => 'decimal:2',
        'porcentaje_descuento' => 'decimal:2',
        'fecha_inicio'         => 'date',
        'fecha_fin'            => 'date',
        'es_activo'            => 'boolean',
    ];

    public function servicios(): BelongsToMany
    {
        return $this->belongsToMany(Servicio::class, 'combo_servicio', 'combo_id', 'servicio_id');
    }

    public function estaVigente(): bool
    {
        if (! $this->es_activo) {
            return false;
        }

        $hoy = now()->toDateString();

        if ($this->fecha_inicio && $this->fecha_inicio->toDateString() > $hoy) {
            return false;
        }

        if ($this->fecha_fin && $this->fecha_fin->toDateString() < $hoy) {
            return false;
        }

        return true;
    }

    public function precioServiciosSueltos(): float
    {
        return (float) $this->servicios->sum('precio_base');
    }
}
