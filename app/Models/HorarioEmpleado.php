<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class HorarioEmpleado extends Model
{
    use HasFactory;

    protected $table = 'horario_empleado';

    protected $fillable = [
        'empleado_id',
        'dia_semana',
        'hora_inicio',
        'hora_fin',
    ];

    protected $casts = [
        'dia_semana'  => 'integer',
        'hora_inicio' => 'string',
        'hora_fin'    => 'string',
    ];

    public function empleado(): BelongsTo
    {
        return $this->belongsTo(Empleado::class, 'empleado_id');
    }

    public function nombreDia(): string
    {
        $dias = ['', 'Lunes', 'Martes', 'Miércoles', 'Jueves', 'Viernes', 'Sábado', 'Domingo'];

        return $dias[$this->dia_semana] ?? '';
    }
}
