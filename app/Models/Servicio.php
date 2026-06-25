<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Servicio extends Model
{
    use HasFactory;

    protected $table = 'servicio';

    protected $fillable = [
        'categoria_servicio_id',
        'nombre',
        'descripcion',
        'precio_base',
        'duracion_minutos',
        'es_activo',
    ];

    protected $casts = [
        'precio_base'     => 'decimal:2',
        'duracion_minutos' => 'integer',
        'es_activo'       => 'boolean',
    ];

    public function categoria(): BelongsTo
    {
        return $this->belongsTo(CategoriaServicio::class, 'categoria_servicio_id');
    }

    public function preciosEmpleado(): HasMany
    {
        return $this->hasMany(PrecioServicioEmpleado::class, 'servicio_id');
    }

    public function combos(): BelongsToMany
    {
        return $this->belongsToMany(Combo::class, 'combo_servicio', 'servicio_id', 'combo_id');
    }

    public function precioParaEmpleado(int $empleadoId): float
    {
        $precio = $this->preciosEmpleado()
            ->where('empleado_id', $empleadoId)
            ->value('precio');

        return (float) ($precio ?? $this->precio_base);
    }
}
