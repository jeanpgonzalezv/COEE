<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Sala extends Model
{
    use HasFactory;

    protected $fillable = [
        'nombre',
        'codigo',
        'activa',
    ];

    protected $casts = [
        'activa' => 'boolean',
    ];

    // ─── Relaciones ───────────────────────────────────────────────

    public function alertas()
    {
        return $this->hasMany(Alerta::class);
    }

    // ─── Scopes ──────────────────────────────────────────────────

    public function scopeActivas($query)
    {
        return $query->where('activa', true);
    }
}