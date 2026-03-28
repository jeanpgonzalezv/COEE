<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'password',
        'rol',
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

    // ─── Relaciones ───────────────────────────────────────────────

    /** Alertas enviadas por este usuario (como profesor) */
    public function alertasEnviadas()
    {
        return $this->hasMany(Alerta::class, 'usuario_id');
    }

    /** Alertas atendidas por este usuario (como administrativo) */
    public function alertasAtendidas()
    {
        return $this->hasMany(Alerta::class, 'atendido_por');
    }

    /** Reportes generados por este usuario */
    public function reportes()
    {
        return $this->hasMany(Reporte::class, 'generado_por');
    }

    // ─── Helpers de rol ──────────────────────────────────────────

    public function esProfesor(): bool
    {
        return $this->rol === 'profesor';
    }

    public function esAdministrativo(): bool
    {
        return in_array($this->rol, ['inspector', 'enfermeria', 'soporte_ti', 'utp']);
    }

    public function esDirectivo(): bool
    {
        return in_array($this->rol, ['director', 'utp']);
    }

    public function getRolLabelAttribute(): string
    {
        return match($this->rol) {
            'profesor'   => 'Profesor/a',
            'inspector'  => 'Inspector/a',
            'enfermeria' => 'Enfermería',
            'soporte_ti' => 'Soporte TI',
            'director'   => 'Director/a',
            'utp'        => 'UTP',
            default      => ucfirst($this->rol),
        };
    }
}
