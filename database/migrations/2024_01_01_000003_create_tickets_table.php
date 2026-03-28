<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tickets', function (Blueprint $table) {
            $table->id();

            $table->foreignId('alerta_id')
                  ->constrained('alertas')
                  ->onDelete('cascade');

            $table->text('solucion_aplicada')->nullable();

            $table->timestamps();

            $table->index('alerta_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tickets');
    }
};