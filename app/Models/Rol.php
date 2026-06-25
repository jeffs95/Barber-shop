<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Rol extends Model
{
    protected $table = 'rol';

    protected $fillable = ['nombre', 'descripcion', 'es_activo'];

    protected $casts = ['es_activo' => 'boolean'];

    public function usuarios()
    {
        return $this->hasMany(Usuario::class);
    }
}
