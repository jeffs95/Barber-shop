<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CitaServicio extends Model
{
    protected $table = 'cita_servicio';

    public $timestamps = false;

    protected $fillable = [
        'cita_id',
        'servicio_id',
        'precio',
    ];

    protected $casts = [
        'precio' => 'decimal:2',
    ];

    public function cita(): BelongsTo
    {
        return $this->belongsTo(Cita::class, 'cita_id');
    }

    public function servicio(): BelongsTo
    {
        return $this->belongsTo(Servicio::class, 'servicio_id');
    }
}
