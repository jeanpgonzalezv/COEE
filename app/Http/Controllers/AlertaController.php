<?php

namespace App\Http\Controllers;

use App\Models\Alerta;
use App\Models\Sala;
use App\Models\Ticket;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AlertaController extends Controller
{
    /**
     * Enviar una nueva alerta desde la sala del profesor.
     * POST /alerta/enviar
     */
    public function enviar(Request $request)
    {
        $request->validate([
            'sala_id' => 'required|exists:salas,id',
            'tipo'    => 'required|in:enfermeria,convivencia,soporte_ti,utp,panico',
            'mensaje' => 'nullable|string|max:500',
        ]);

        // Verificar que la sala esté activa
        $sala = Sala::findOrFail($request->sala_id);
        if (!$sala->activa) {
            return response()->json([
                'success' => false,
                'message' => 'La sala seleccionada no está activa.',
            ], 422);
        }

        // Verificar que no haya una alerta pendiente de este tipo en esta sala
        $alertaExistente = Alerta::where('sala_id', $request->sala_id)
            ->where('tipo', $request->tipo)
            ->whereIn('estado', ['pendiente', 'en_atencion'])
            ->first();

        if ($alertaExistente) {
            return response()->json([
                'success' => false,
                'message' => 'Ya existe una alerta activa de ese tipo en esta sala.',
            ], 422);
        }

        $alerta = Alerta::create([
            'sala_id'        => $request->sala_id,
            'usuario_id'     => Auth::id(),
            'tipo'           => $request->tipo,
            'estado'         => 'pendiente',
            'mensaje'        => $request->mensaje,
            'fecha_creacion' => now(),
        ]);

        return response()->json([
            'success' => true,
            'message' => '✅ Alerta enviada correctamente. El equipo ha sido notificado.',
            'alerta'  => $alerta->load('sala'),
        ]);
    }

    /**
     * Atender una alerta (administrativo hace clic en "Atender").
     * POST /alerta/{alerta}/atender
     */
    public function atender(Alerta $alerta)
    {
        // Solo se pueden atender alertas pendientes
        if ($alerta->estado !== 'pendiente') {
            return response()->json([
                'success' => false,
                'message' => 'Esta alerta ya está siendo atendida o fue cerrada.',
            ], 422);
        }

        $alerta->update([
            'estado'         => 'en_atencion',
            'atendido_por'   => Auth::id(),
            'fecha_atencion' => now(),
        ]);

        return response()->json([
            'success' => true,
            'message' => '🟡 Alerta marcada como EN ATENCIÓN.',
            'alerta'  => $alerta->fresh()->load(['sala', 'usuario', 'atendidoPor']),
        ]);
    }

    /**
     * Resolver una alerta y crear/actualizar ticket.
     * POST /alerta/{alerta}/resolver
     */
    public function resolver(Request $request, Alerta $alerta)
    {
        $request->validate([
            'solucion_aplicada' => 'nullable|string|max:1000',
        ]);

        if (!in_array($alerta->estado, ['en_atencion', 'pendiente'])) {
            return response()->json([
                'success' => false,
                'message' => 'Esta alerta ya fue resuelta o cerrada.',
            ], 422);
        }

        $alerta->update([
            'estado'       => 'resuelto',
            'fecha_cierre' => now(),
        ]);

        // Crear o actualizar ticket
        Ticket::updateOrCreate(
            ['alerta_id' => $alerta->id],
            ['solucion_aplicada' => $request->solucion_aplicada]
        );

        return response()->json([
            'success' => true,
            'message' => '✅ Alerta resuelta correctamente.',
        ]);
    }

    /**
     * Cerrar definitivamente una alerta.
     * POST /alerta/{alerta}/cerrar
     */
    public function cerrar(Alerta $alerta)
    {
        $alerta->update([
            'estado'       => 'cerrado',
            'fecha_cierre' => $alerta->fecha_cierre ?? now(),
        ]);

        return response()->json([
            'success' => true,
            'message' => '🔒 Alerta cerrada.',
        ]);
    }

    /**
     * Obtener alertas pendientes (para polling AJAX).
     * GET /api/alertas/pendientes
     */
    public function pendientes()
    {
        $alertas = Alerta::with(['sala', 'usuario'])
            ->whereIn('estado', ['pendiente', 'en_atencion'])
            ->orderBy('tipo', 'desc') // pánico primero por orden alfabético
            ->orderBy('fecha_creacion', 'asc')
            ->get()
            ->map(function ($alerta) {
                return [
                    'id'                  => $alerta->id,
                    'sala'                => $alerta->sala->nombre,
                    'sala_codigo'         => $alerta->sala->codigo,
                    'tipo'                => $alerta->tipo,
                    'tipo_label'          => $alerta->tipo_label,
                    'tipo_color'          => $alerta->tipo_color,
                    'estado'              => $alerta->estado,
                    'estado_label'        => $alerta->estado_label,
                    'usuario'             => $alerta->usuario->name,
                    'mensaje'             => $alerta->mensaje,
                    'tiempo_transcurrido' => $alerta->tiempo_transcurrido,
                    'fecha_creacion'      => $alerta->fecha_creacion->format('H:i'),
                ];
            });

        return response()->json([
            'alertas' => $alertas,
            'total'   => $alertas->count(),
        ]);
    }

    /**
     * Historial de alertas del profesor autenticado.
     * GET /sala/historial
     */
    public function historialProfesor()
    {
        $alertas = Alerta::with(['sala', 'ticket'])
            ->where('usuario_id', Auth::id())
            ->orderBy('fecha_creacion', 'desc')
            ->paginate(20);

        return view('sala.historial', compact('alertas'));
    }
}