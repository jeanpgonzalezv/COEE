<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('reportes', function (Blueprint $table) {
            $table->id();

            $table->enum('tipo', [
                'diario',
                'semanal',
                'mensual',
                'personalizado'
            ]);

            $table->date('fecha_inicio');
            $table->date('fecha_fin');
            $table->json('datos')->nullable();

            $table->foreignId('generado_por')
                  ->constrained('users')
                  ->onDelete('cascade');

            $table->timestamps();

            $table->index('tipo');
            $table->index('generado_por');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('reportes');
    }
};