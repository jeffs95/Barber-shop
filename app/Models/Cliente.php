<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Cliente extends Model
{
    use HasFactory, SoftDeletes;

    protected $table = 'cliente';

    protected $fillable = [
        'nombre',
        'apellido',
        'telefono',
        'email',
        'foto',
        'fecha_nacimiento',
        'tipo',
        'notas',
        'puntos_fidelidad',
    ];

    protected $casts = [
        'fecha_nacimiento' => 'date',
        'puntos_fidelidad' => 'integer',
    ];

    public function citas(): HasMany
    {
        return $this->hasMany(Cita::class, 'cliente_id');
    }

    public function nombreCompleto(): string
    {
        return trim("{$this->nombre} {$this->apellido}");
    }
}
