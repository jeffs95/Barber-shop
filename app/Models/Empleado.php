<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Empleado extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'empleado';

    protected $fillable = [
        'usuario_id',
        'sucursal_id',
        'rol',
        'porcentaje_comision',
        'color_agenda',
        'es_activo',
        'telefono',
        'direccion',
        'fecha_nacimiento',
        'sueldo_base',
        'fecha_contratacion',
        'tipo_contrato',
    ];

    protected $casts = [
        'es_activo'           => 'boolean',
        'porcentaje_comision' => 'decimal:2',
        'sueldo_base'         => 'decimal:2',
        'fecha_nacimiento'    => 'date',
        'fecha_contratacion'  => 'date',
    ];

    public function usuario(): BelongsTo
    {
        return $this->belongsTo(Usuario::class, 'usuario_id');
    }

    public function sucursal(): BelongsTo
    {
        return $this->belongsTo(Sucursal::class, 'sucursal_id');
    }

    public function horarios(): HasMany
    {
        return $this->hasMany(HorarioEmpleado::class, 'empleado_id');
    }

    public function preciosServicio(): HasMany
    {
        return $this->hasMany(PrecioServicioEmpleado::class, 'empleado_id');
    }

    public function citas(): HasMany
    {
        return $this->hasMany(Cita::class, 'empleado_id');
    }

    public function getNombreCompletoAttribute(): string
    {
        return $this->usuario?->getFilamentName() ?? 'Sin nombre';
    }
}
