<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        $usuarios = [
            // Directivos
            [
                'name'     => 'Carlos Rodríguez',
                'email'    => 'director@coee.cl',
                'password' => Hash::make('password'),
                'rol'      => 'director',
            ],
            [
                'name'     => 'Ana González',
                'email'    => 'utp@coee.cl',
                'password' => Hash::make('password'),
                'rol'      => 'utp',
            ],
            // Administrativos
            [
                'name'     => 'Pedro Martínez',
                'email'    => 'inspector@coee.cl',
                'password' => Hash::make('password'),
                'rol'      => 'inspector',
            ],
            [
                'name'     => 'María Soto',
                'email'    => 'enfermeria@coee.cl',
                'password' => Hash::make('password'),
                'rol'      => 'enfermeria',
            ],
            [
                'name'     => 'Jorge López',
                'email'    => 'soporte@coee.cl',
                'password' => Hash::make('password'),
                'rol'      => 'soporte_ti',
            ],
            // Profesores
            [
                'name'     => 'Valentina Torres',
                'email'    => 'profesor1@coee.cl',
                'password' => Hash::make('password'),
                'rol'      => 'profesor',
            ],
            [
                'name'     => 'Roberto Díaz',
                'email'    => 'profesor2@coee.cl',
                'password' => Hash::make('password'),
                'rol'      => 'profesor',
            ],
            [
                'name'     => 'Camila Fuentes',
                'email'    => 'profesor3@coee.cl',
                'password' => Hash::make('password'),
                'rol'      => 'profesor',
            ],
        ];

        foreach ($usuarios as $usuario) {
            User::updateOrCreate(
                ['email' => $usuario['email']],
                $usuario
            );
        }

        $this->command->info('✅ Usuarios creados correctamente');
    }
}