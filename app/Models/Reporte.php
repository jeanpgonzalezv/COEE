<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Reporte extends Model
{
    use HasFactory;

    protected $fillable = [
        'tipo',
        'fecha_inicio',
        'fecha_fin',
        'datos',
        'generado_por',
    ];

    protected $casts = [
        'fecha_inicio' => 'date',
        'fecha_fin'    => 'date',
        'datos'        => 'array',
    ];

    // ─── Relaciones ───────────────────────────────────────────────

    public function generadoPor()
    {
        return $this->belongsTo(User::class, 'generado_por');
    }
}