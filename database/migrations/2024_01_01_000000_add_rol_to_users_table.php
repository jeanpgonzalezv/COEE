<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Si ya existe la tabla users (de Breeze), solo agregamos el campo rol
        Schema::table('users', function (Blueprint $table) {
            $table->enum('rol', [
                'profesor',
                'inspector',
                'enfermeria',
                'soporte_ti',
                'director',
                'utp'
            ])->default('profesor')->after('email');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('rol');
        });
    }
};