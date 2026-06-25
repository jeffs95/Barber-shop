<?php

namespace App\Models;

use Database\Factories\UsuarioFactory;
use Filament\Models\Contracts\FilamentUser;
use Filament\Models\Contracts\HasName;
use Filament\Panel;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class Usuario extends Authenticatable implements FilamentUser, HasName
{
    /** @use HasFactory<UsuarioFactory> */
    use HasFactory, Notifiable;

    protected $table = 'usuario';

    protected $fillable = [
        'rol_id',
        'sucursal_id',
        'nombre',
        'apellido',
        'email',
        'password',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password'          => 'hashed',
        ];
    }

    public function getFilamentName(): string
    {
        return trim("{$this->nombre} {$this->apellido}");
    }

    public function canAccessPanel(Panel $panel): bool
    {
        return in_array($this->rol?->nombre, ['dueño', 'admin_sucursal', 'recepcionista']);
    }

    public function rol(): BelongsTo
    {
        return $this->belongsTo(Rol::class);
    }

    public function sucursal(): BelongsTo
    {
        return $this->belongsTo(Sucursal::class);
    }

    public function empleado(): HasOne
    {
        return $this->hasOne(Empleado::class, 'usuario_id');
    }

    // ── Helpers de rol ──────────────────────────────────────────────────

    public function esDuenio(): bool
    {
        return $this->rol?->nombre === 'dueño';
    }

    public function esAdminSucursal(): bool
    {
        return $this->rol?->nombre === 'admin_sucursal';
    }

    public function esBarbero(): bool
    {
        return $this->rol?->nombre === 'barbero';
    }

    /** ID de la sucursal asignada (null = acceso global) */
    public function getSucursalId(): ?int
    {
        return $this->sucursal_id;
    }
}
