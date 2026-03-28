<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('salas', function (Blueprint $table) {
            $table->id();
            $table->string('nombre');                          // ej: "Sala 1A"
            $table->string('codigo')->unique();                // ej: "SAL-01"
            $table->boolean('activa')->default(true);
            $table->timestamps();

            $table->index('activa');
            $table->index('codigo');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('salas');
    }
};