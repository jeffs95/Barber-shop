<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Proveedor extends Model
{
    protected $table = 'proveedor';

    protected $fillable = [
        'nombre',
        'contacto',
        'telefono',
        'email',
        'notas',
        'es_activo',
    ];

    protected $casts = [
        'es_activo' => 'boolean',
    ];

    public function productos(): HasMany
    {
        return $this->hasMany(Producto::class, 'proveedor_id');
    }
}
