<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('alertas', function (Blueprint $table) {
            $table->id();

            $table->foreignId('sala_id')
                  ->constrained('salas')
                  ->onDelete('cascade');

            $table->foreignId('usuario_id')
                  ->constrained('users')
                  ->onDelete('cascade');

            $table->enum('tipo', [
                'enfermeria',
                'convivencia',
                'soporte_ti',
                'utp',
                'panico'
            ]);

            $table->enum('estado', [
                'pendiente',
                'en_atencion',
                'resuelto',
                'cerrado'
            ])->default('pendiente');

            $table->foreignId('atendido_por')
                  ->nullable()
                  ->constrained('users')
                  ->onDelete('set null');

            $table->text('mensaje')->nullable();

            $table->timestamp('fecha_creacion')->useCurrent();
            $table->timestamp('fecha_atencion')->nullable();
            $table->timestamp('fecha_cierre')->nullable();

            $table->timestamps();

            // Índices para mejorar rendimiento en consultas frecuentes
            $table->index('estado');
            $table->index('tipo');
            $table->index('sala_id');
            $table->index('usuario_id');
            $table->index('fecha_creacion');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('alertas');
    }
};