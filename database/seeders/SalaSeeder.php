<?php

namespace Database\Seeders;

use App\Models\Sala;
use Illuminate\Database\Seeder;

class SalaSeeder extends Seeder
{
    public function run(): void
    {
        $salas = [
            ['nombre' => 'Sala 1A',          'codigo' => 'SAL-01A'],
            ['nombre' => 'Sala 1B',          'codigo' => 'SAL-01B'],
            ['nombre' => 'Sala 2A',          'codigo' => 'SAL-02A'],
            ['nombre' => 'Sala 2B',          'codigo' => 'SAL-02B'],
            ['nombre' => 'Sala 3A',          'codigo' => 'SAL-03A'],
            ['nombre' => 'Sala 3B',          'codigo' => 'SAL-03B'],
            ['nombre' => 'Sala 4A',          'codigo' => 'SAL-04A'],
            ['nombre' => 'Sala 4B',          'codigo' => 'SAL-04B'],
            ['nombre' => 'Laboratorio TI 1', 'codigo' => 'LAB-TI1'],
            ['nombre' => 'Laboratorio TI 2', 'codigo' => 'LAB-TI2'],
            ['nombre' => 'Laboratorio Ciencias', 'codigo' => 'LAB-CIE'],
            ['nombre' => 'Sala Multiuso',    'codigo' => 'SAL-MUL'],
            ['nombre' => 'Biblioteca',       'codigo' => 'BIB-001'],
            ['nombre' => 'Sala de Artes',    'codigo' => 'SAL-ART'],
            ['nombre' => 'Gimnasio',         'codigo' => 'GIM-001'],
        ];

        foreach ($salas as $sala) {
            Sala::updateOrCreate(
                ['codigo' => $sala['codigo']],
                array_merge($sala, ['activa' => true])
            );
        }

        $this->command->info('✅ Salas creadas correctamente');
    }
}