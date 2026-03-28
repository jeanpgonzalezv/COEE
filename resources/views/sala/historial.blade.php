@extends('layouts.app')

@section('title', 'Mi Historial de Alertas')

@section('content')
<div class="card shadow-sm">
    <div class="card-header">
        <h5 class="mb-0">
            <i class="bi bi-clock-history me-2"></i>
            Mi Historial de Alertas
        </h5>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead class="table-dark">
                    <tr>
                        <th>#</th>
                        <th>Sala</th>
                        <th>Tipo</th>
                        <th>Estado</th>
                        <th>Fecha</th>
                        <th>Tiempo Respuesta</th>
                        <th>Solución</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($alertas as $alerta)
                        <tr>
                            <td class="text-muted small">{{ $alerta->id }}</td>
                            <td>
                                <strong>{{ $alerta->sala->nombre }}</strong><br>
                                <small class="text-muted">{{ $alerta->sala->codigo }}</small>
                            </td>
                            <td>
                                <span class="badge bg-{{ $alerta->tipo_color }}">
                                    {{ $alerta->tipo_label }}
                                </span>
                            </td>
                            <td>
                                <span class="badge bg-{{ match($alerta->estado) {
                                    'pendiente'   => 'warning text-dark',
                                    'en_atencion' => 'info',
                                    'resuelto'    => 'success',
                                    'cerrado'     => 'secondary',
                                    default       => 'light text-dark'
                                } }}">
                                    {{ $alerta->estado_label }}
                                </span>
                            </td>
                            <td>
                                <small>{{ $alerta->fecha_creacion->format('d/m/Y H:i') }}</small>
                            </td>
                            <td>
                                @if($alerta->tiempo_respuesta !== null)
                                    <span class="badge bg-light text-dark">
                                        {{ $alerta->tiempo_respuesta }} min
                                    </span>
                                @else
                                    <span class="text-muted">—</span>
                                @endif
                            </td>
                            <td>
                                @if($alerta->ticket?->solucion_aplicada)
                                    <span class="text-success">
                                        <i class="bi bi-check-circle me-1"></i>
                                        <small>{{ Str::limit($alerta->ticket->solucion_aplicada, 60) }}</small>
                                    </span>
                                @else
                                    <span class="text-muted small">—</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center py-5 text-muted">
                                <i class="bi bi-inbox fs-1 d-block mb-2"></i>
                                No has enviado alertas aún
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    @if($alertas->hasPages())
        <div class="card-footer">
            {{ $alertas->links() }}
        </div>
    @endif
</div>
@endsection