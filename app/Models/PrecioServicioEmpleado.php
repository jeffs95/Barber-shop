<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PrecioServicioEmpleado extends Model
{
    protected $table = 'precio_servicio_empleado';

    protected $fillable = [
        'servicio_id',
        'empleado_id',
        'precio',
    ];

    protected $casts = [
        'precio' => 'decimal:2',
    ];

    public function servicio(): BelongsTo
    {
        return $this->belongsTo(Servicio::class, 'servicio_id');
    }

    public function empleado(): BelongsTo
    {
        return $this->belongsTo(Empleado::class, 'empleado_id');
    }
}
