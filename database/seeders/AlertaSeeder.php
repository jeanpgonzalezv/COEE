<?php

namespace Database\Seeders;

use App\Models\Alerta;
use App\Models\Sala;
use App\Models\Ticket;
use App\Models\User;
use Illuminate\Database\Seeder;

class AlertaSeeder extends Seeder
{
    public function run(): void
    {
        $profesores     = User::where('rol', 'profesor')->get();
        $administrativos = User::whereIn('rol', ['inspector', 'enfermeria', 'soporte_ti'])->get();
        $salas          = Sala::all();

        $tipos  = ['enfermeria', 'convivencia', 'soporte_ti', 'utp', 'panico'];
        $estados = ['pendiente', 'en_atencion', 'resuelto', 'cerrado'];

        // Crear 40 alertas de prueba en los últimos 30 días
        for ($i = 0; $i < 40; $i++) {
            $tipo   = $tipos[array_rand($tipos)];
            $estado = $estados[array_rand($estados)];
            $sala   = $salas->random();
            $prof   = $profesores->random();
            $admin  = $administrativos->random();

            $fechaCreacion = now()->subDays(rand(0, 30))->subMinutes(rand(0, 480));

            $fechaAtencion = null;
            $fechaCierre   = null;
            $atendidoPor   = null;

            if (in_array($estado, ['en_atencion', 'resuelto', 'cerrado'])) {
                $atendidoPor   = $admin->id;
                $fechaAtencion = (clone $fechaCreacion)->addMinutes(rand(2, 30));
            }

            if (in_array($estado, ['resuelto', 'cerrado'])) {
                $fechaCierre = (clone $fechaAtencion)->addMinutes(rand(5, 60));
            }

            $alerta = Alerta::create([
                'sala_id'        => $sala->id,
                'usuario_id'     => $prof->id,
                'tipo'           => $tipo,
                'estado'         => $estado,
                'atendido_por'   => $atendidoPor,
                'mensaje'        => $this->getMensajeEjemplo($tipo),
                'fecha_creacion' => $fechaCreacion,
                'fecha_atencion' => $fechaAtencion,
                'fecha_cierre'   => $fechaCierre,
            ]);

            // Crear ticket si la alerta está resuelta o cerrada
            if (in_array($estado, ['resuelto', 'cerrado'])) {
                Ticket::create([
                    'alerta_id'          => $alerta->id,
                    'solucion_aplicada'  => $this->getSolucionEjemplo($tipo),
                ]);
            }
        }

        $this->command->info('✅ Alertas de prueba creadas correctamente');
    }

    private function getMensajeEjemplo(string $tipo): string
    {
        return match($tipo) {
            'enfermeria'  => 'Alumno presenta malestar estomacal, requiere atención.',
            'convivencia' => 'Situación de conflicto entre estudiantes en el aula.',
            'soporte_ti'  => 'El proyector no enciende, necesito soporte técnico.',
            'utp'         => 'Necesito apoyo pedagógico, alumno con dificultades.',
            'panico'      => '¡EMERGENCIA! Requiero ayuda inmediata en la sala.',
            default       => 'Solicitud de ayuda.',
        };
    }

    private function getSolucionEjemplo(string $tipo): string
    {
        return match($tipo) {
            'enfermeria'  => 'Se atendió al alumno, se le administró medicamento. Padres notificados.',
            'convivencia' => 'Se intervino el conflicto y se derivó a orientación.',
            'soporte_ti'  => 'Se reinició el proyector y se actualizó el driver.',
            'utp'         => 'Se coordinó apoyo pedagógico diferenciado para el estudiante.',
            'panico'      => 'Se activó protocolo de emergencia. Situación controlada.',
            default       => 'Situación resuelta satisfactoriamente.',
        };
    }
}