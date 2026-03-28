<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Alerta extends Model
{
    use HasFactory;

    protected $fillable = [
        'sala_id',
        'usuario_id',
        'tipo',
        'estado',
        'atendido_por',
        'mensaje',
        'fecha_creacion',
        'fecha_atencion',
        'fecha_cierre',
    ];

    protected $casts = [
        'fecha_creacion' => 'datetime',
        'fecha_atencion' => 'datetime',
        'fecha_cierre'   => 'datetime',
    ];

    // ─── Relaciones ───────────────────────────────────────────────

    public function sala()
    {
        return $this->belongsTo(Sala::class);
    }

    public function usuario()
    {
        return $this->belongsTo(User::class, 'usuario_id');
    }

    public function atendidoPor()
    {
        return $this->belongsTo(User::class, 'atendido_por');
    }

    public function ticket()
    {
        return $this->hasOne(Ticket::class);
    }

    // ─── Scopes ──────────────────────────────────────────────────

    public function scopePendientes($query)
    {
        return $query->where('estado', 'pendiente');
    }

    public function scopeEnAtencion($query)
    {
        return $query->where('estado', 'en_atencion');
    }

    public function scopeActivas($query)
    {
        return $query->whereIn('estado', ['pendiente', 'en_atencion']);
    }

    // ─── Helpers ─────────────────────────────────────────────────

    /**
     * Retorna el label legible del tipo de alerta
     */
    public function getTipoLabelAttribute(): string
    {
        return match($this->tipo) {
            'enfermeria'  => 'Enfermería',
            'convivencia' => 'Convivencia / PIE',
            'soporte_ti'  => 'Soporte TI',
            'utp'         => 'UTP / Inspectoría',
            'panico'      => '⚠️ PÁNICO',
            default       => ucfirst($this->tipo),
        };
    }

    /**
     * Retorna la clase Bootstrap de color según tipo
     */
    public function getTipoColorAttribute(): string
    {
        return match($this->tipo) {
            'enfermeria'  => 'success',
            'convivencia' => 'warning',
            'soporte_ti'  => 'info',
            'utp'         => 'primary',
            'panico'      => 'danger',
            default       => 'secondary',
        };
    }

    /**
     * Retorna el label del estado
     */
    public function getEstadoLabelAttribute(): string
    {
        return match($this->estado) {
            'pendiente'   => 'Pendiente',
            'en_atencion' => 'En Atención',
            'resuelto'    => 'Resuelto',
            'cerrado'     => 'Cerrado',
            default       => ucfirst($this->estado),
        };
    }

    /**
     * Tiempo transcurrido desde la creación (en minutos)
     */
    public function getTiempoTranscurridoAttribute(): int
    {
        return (int) now()->diffInMinutes($this->fecha_creacion);
    }

    /**
     * Tiempo de respuesta (entre creación y atención) en minutos
     */
    public function getTiempoRespuestaAttribute(): ?int
    {
        if (!$this->fecha_atencion) return null;
        return (int) $this->fecha_atencion->diffInMinutes($this->fecha_creacion);
    }
}