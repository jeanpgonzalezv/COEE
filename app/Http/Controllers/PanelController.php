<?php

namespace App\Http\Controllers;

use App\Models\Alerta;
use App\Models\Sala;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PanelController extends Controller
{
    /**
     * Dashboard del Profesor — muestra botones y su sala asignada.
     * GET /sala/dashboard
     */
    public function salaProfesor()
    {
        $salas   = Sala::activas()->orderBy('nombre')->get();
        $usuario = Auth::user();

        // Alertas activas del profesor (pendientes/en atención)
        $alertasActivas = Alerta::with('sala')
            ->where('usuario_id', $usuario->id)
            ->whereIn('estado', ['pendiente', 'en_atencion'])
            ->orderBy('fecha_creacion', 'desc')
            ->get();

        return view('sala.dashboard', compact('salas', 'alertasActivas'));
    }

    /**
     * Panel de alertas para administrativos (inspector, enfermería, soporte_ti, utp).
     * GET /admin/panel
     */
    public function panelAdministrativo()
    {
        // Alertas activas (pendientes y en atención)
        $alertasPendientes = Alerta::with(['sala', 'usuario'])
            ->where('estado', 'pendiente')
            ->orderByRaw("FIELD(tipo, 'panico', 'enfermeria', 'convivencia', 'utp', 'soporte_ti')")
            ->orderBy('fecha_creacion', 'asc')
            ->get();

        $alertasEnAtencion = Alerta::with(['sala', 'usuario', 'atendidoPor'])
            ->where('estado', 'en_atencion')
            ->orderBy('fecha_atencion', 'desc')
            ->get();

        return view('admin.panel', compact('alertasPendientes', 'alertasEnAtencion'));
    }

    /**
     * Dashboard de Director / UTP con gráficos y estadísticas.
     * GET /admin/dashboard
     */
    public function dashboardDirector()
    {
        $hoy   = now();
        $hace30 = $hoy->copy()->subDays(30);

        // ── Alertas por tipo (últimos 30 días) ──
        $alertasPorTipo = Alerta::selectRaw('tipo, COUNT(*) as total')
            ->where('fecha_creacion', '>=', $hace30)
            ->groupBy('tipo')
            ->pluck('total', 'tipo')
            ->toArray();

        // ── Top 10 salas con más alertas ──
        $alertasPorSala = Alerta::with('sala')
            ->selectRaw('sala_id, COUNT(*) as total')
            ->where('fecha_creacion', '>=', $hace30)
            ->groupBy('sala_id')
            ->orderByDesc('total')
            ->limit(10)
            ->get()
            ->map(fn($a) => [
                'sala'  => $a->sala->nombre ?? 'Sin sala',
                'total' => $a->total,
            ]);

        // ── Alertas por día (últimos 30 días) ──
        $alertasPorDia = Alerta::selectRaw('DATE(fecha_creacion) as dia, COUNT(*) as total')
            ->where('fecha_creacion', '>=', $hace30)
            ->groupBy('dia')
            ->orderBy('dia')
            ->pluck('total', 'dia')
            ->toArray();

        // Rellenar días sin alertas
        $diasLabels  = [];
        $diasData    = [];
        for ($i = 29; $i >= 0; $i--) {
            $fecha        = $hoy->copy()->subDays($i)->format('Y-m-d');
            $diasLabels[] = $hoy->copy()->subDays($i)->format('d/m');
            $diasData[]   = $alertasPorDia[$fecha] ?? 0;
        }

        // ── Tiempo promedio de respuesta (en minutos) ──
        $tiempoPromedio = Alerta::whereNotNull('fecha_atencion')
            ->where('fecha_creacion', '>=', $hace30)
            ->selectRaw('AVG(TIMESTAMPDIFF(MINUTE, fecha_creacion, fecha_atencion)) as promedio')
            ->value('promedio');

        $tiempoPromedio = $tiempoPromedio ? round($tiempoPromedio, 1) : 0;

        // ── Totales generales ──
        $totalAlertas    = Alerta::where('fecha_creacion', '>=', $hace30)->count();
        $totalResueltas  = Alerta::where('fecha_creacion', '>=', $hace30)
                              ->whereIn('estado', ['resuelto', 'cerrado'])->count();
        $totalPendientes = Alerta::where('fecha_creacion', '>=', $hace30)
                              ->where('estado', 'pendiente')->count();

        return view('admin.dashboard', compact(
            'alertasPorTipo',
            'alertasPorSala',
            'diasLabels',
            'diasData',
            'tiempoPromedio',
            'totalAlertas',
            'totalResueltas',
            'totalPendientes'
        ));
    }

    /**
     * Historial completo de tickets.
     * GET /admin/historial
     */
    public function historial(Request $request)
    {
        $query = Alerta::with(['sala', 'usuario', 'atendidoPor', 'ticket'])
            ->orderBy('fecha_creacion', 'desc');

        // Filtros opcionales
        if ($request->filled('tipo')) {
            $query->where('tipo', $request->tipo);
        }
        if ($request->filled('estado')) {
            $query->where('estado', $request->estado);
        }
        if ($request->filled('sala_id')) {
            $query->where('sala_id', $request->sala_id);
        }
        if ($request->filled('fecha_inicio') && $request->filled('fecha_fin')) {
            $query->whereBetween('fecha_creacion', [
                $request->fecha_inicio . ' 00:00:00',
                $request->fecha_fin   . ' 23:59:59',
            ]);
        }

        $alertas = $query->paginate(20)->withQueryString();
        $salas   = Sala::activas()->orderBy('nombre')->get();

        return view('admin.historial', compact('alertas', 'salas'));
    }
}