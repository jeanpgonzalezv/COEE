<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Ticket extends Model
{
    use HasFactory;

    protected $fillable = [
        'alerta_id',
        'solucion_aplicada',
    ];

    // ─── Relaciones ───────────────────────────────────────────────

    public function alerta()
    {
        return $this->belongsTo(Alerta::class);
    }
}