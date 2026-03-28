<?php

namespace App\Http\Controllers;

use App\Models\Alerta;
use App\Models\Reporte;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ReporteController extends Controller
{
    /**
     * Exportar alertas a CSV (alternativa sin Laravel Excel).
     * GET /admin/exportar-csv
     */
    public function exportarCsv(Request $request)
    {
        $request->validate([
            'fecha_inicio' => 'required|date',
            'fecha_fin'    => 'required|date|after_or_equal:fecha_inicio',
        ]);

        $alertas = Alerta::with(['sala', 'usuario', 'atendidoPor', 'ticket'])
            ->whereBetween('fecha_creacion', [
                $request->fecha_inicio . ' 00:00:00',
                $request->fecha_fin   . ' 23:59:59',
            ])
            ->orderBy('fecha_creacion', 'desc')
            ->get();

        // Guardar reporte en la base de datos
        Reporte::create([
            'tipo'         => 'personalizado',
            'fecha_inicio' => $request->fecha_inicio,
            'fecha_fin'    => $request->fecha_fin,
            'datos'        => [
                'total'       => $alertas->count(),
                'por_tipo'    => $alertas->groupBy('tipo')->map->count(),
                'por_estado'  => $alertas->groupBy('estado')->map->count(),
            ],
            'generado_por' => Auth::id(),
        ]);

        // Generar CSV
        $filename = 'reporte_alertas_' . now()->format('Ymd_His') . '.csv';
        $headers  = [
            'Content-Type'        => 'text/csv; charset=UTF-8',
            'Content-Disposition' => "attachment; filename=\"$filename\"",
        ];

        $callback = function () use ($alertas) {
            $handle = fopen('php://output', 'w');

            // BOM para Excel (evita problemas con caracteres especiales)
            fwrite($handle, "\xEF\xBB\xBF");

            // Cabeceras CSV
            fputcsv($handle, [
                'ID',
                'Sala',
                'Código Sala',
                'Tipo',
                'Estado',
                'Enviado Por',
                'Atendido Por',
                'Mensaje',
                'Fecha Creación',
                'Fecha Atención',
                'Fecha Cierre',
                'Tiempo Respuesta (min)',
                'Solución Aplicada',
            ], ';');

            foreach ($alertas as $alerta) {
                fputcsv($handle, [
                    $alerta->id,
                    $alerta->sala->nombre ?? '',
                    $alerta->sala->codigo ?? '',
                    $alerta->tipo_label,
                    $alerta->estado_label,
                    $alerta->usuario->name ?? '',
                    $alerta->atendidoPor->name ?? '',
                    $alerta->mensaje ?? '',
                    $alerta->fecha_creacion?->format('d/m/Y H:i'),
                    $alerta->fecha_atencion?->format('d/m/Y H:i'),
                    $alerta->fecha_cierre?->format('d/m/Y H:i'),
                    $alerta->tiempo_respuesta ?? '',
                    $alerta->ticket->solucion_aplicada ?? '',
                ], ';');
            }

            fclose($handle);
        };

        return response()->stream($callback, 200, $headers);
    }
}