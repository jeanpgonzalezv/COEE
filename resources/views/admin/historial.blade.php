@extends('layouts.app')

@section('title', 'Historial de Tickets')

@section('content')

{{-- ── FILTROS ── --}}
<div class="card shadow-sm mb-4">
    <div class="card-body">
        <form method="GET" action="{{ route('admin.historial') }}" class="row g-3 align-items-end">
            <div class="col-md-2">
                <label class="form-label small">Tipo</label>
                <select name="tipo" class="form-select form-select-sm">
                    <option value="">Todos</option>
                    <option value="enfermeria"  {{ request('tipo') === 'enfermeria'  ? 'selected' : '' }}>Enfermería</option>
                    <option value="convivencia" {{ request('tipo') === 'convivencia' ? 'selected' : '' }}>Convivencia</option>
                    <option value="soporte_ti"  {{ request('tipo') === 'soporte_ti'  ? 'selected' : '' }}>Soporte TI</option>
                    <option value="utp"         {{ request('tipo') === 'utp'         ? 'selected' : '' }}>UTP</option>
                    <option value="panico"      {{ request('tipo') === 'panico'      ? 'selected' : '' }}>Pánico</option>
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label small">Estado</label>
                <select name="estado" class="form-select form-select-sm">
                    <option value="">Todos</option>
                    <option value="pendiente"   {{ request('estado') === 'pendiente'   ? 'selected' : '' }}>Pendiente</option>
                    <option value="en_atencion" {{ request('estado') === 'en_atencion' ? 'selected' : '' }}>En Atención</option>
                    <option value="resuelto"    {{ request('estado') === 'resuelto'    ? 'selected' : '' }}>Resuelto</option>
                    <option value="cerrado"     {{ request('estado') === 'cerrado'     ? 'selected' : '' }}>Cerrado</option>
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label small">Sala</label>
                <select name="sala_id" class="form-select form-select-sm">
                    <option value="">Todas</option>
                    @foreach($salas as $sala)
                        <option value="{{ $sala->id }}" {{ request('sala_id') == $sala->id ? 'selected' : '' }}>
                            {{ $sala->nombre }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2">
                <label class="form-label small">Desde</label>
                <input type="date" name="fecha_inicio" class="form-control form-control-sm"
                       value="{{ request('fecha_inicio') }}">
            </div>
            <div class="col-md-2">
                <label class="form-label small">Hasta</label>
                <input type="date" name="fecha_fin" class="form-control form-control-sm"
                       value="{{ request('fecha_fin') }}">
            </div>
            <div class="col-md-2">
                <button type="submit" class="btn btn-primary btn-sm w-100">
                    <i class="bi bi-funnel me-1"></i> Filtrar
                </button>
                <a href="{{ route('admin.historial') }}" class="btn btn-outline-secondary btn-sm w-100 mt-1">
                    Limpiar
                </a>
            </div>
        </form>
    </div>
</div>

{{-- ── TABLA ── --}}
<div class="card shadow-sm">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h6 class="mb-0">
            <i class="bi bi-journal-text me-2"></i>
            Historial de Alertas / Tickets
        </h6>
        <span class="badge bg-secondary">{{ $alertas->total() }} registros</span>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover table-sm mb-0">
                <thead class="table-dark">
                    <tr>
                        <th>#</th>
                        <th>Sala</th>
                        <th>Tipo</th>
                        <th>Estado</th>
                        <th>Enviado por</th>
                        <th>Atendido por</th>
                        <th>Creación</th>
                        <th>Resp. (min)</th>
                        <th>Solución</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($alertas as $alerta)
                        <tr>
                            <td class="text-muted small">{{ $alerta->id }}</td>
                            <td>
                                <strong class="d-block">{{ $alerta->sala->nombre }}</strong>
                                <small class="text-muted">{{ $alerta->sala->codigo }}</small>
                            </td>
                            <td>
                                <span class="badge bg-{{ $alerta->tipo_color }}">
                                    {{ $alerta->tipo_label }}
                                </span>
                            </td>
                            <td>
                                @php
                                    $estadoColor = match($alerta->estado) {
                                        'pendiente'   => 'warning text-dark',
                                        'en_atencion' => 'info',
                                        'resuelto'    => 'success',
                                        'cerrado'     => 'secondary',
                                        default       => 'light text-dark'
                                    };
                                @endphp
                                <span class="badge bg-{{ $estadoColor }}">
                                    {{ $alerta->estado_label }}
                                </span>
                            </td>
                            <td><small>{{ $alerta->usuario->name ?? '—' }}</small></td>
                            <td><small>{{ $alerta->atendidoPor->name ?? '—' }}</small></td>
                            <td><small>{{ $alerta->fecha_creacion->format('d/m/Y H:i') }}</small></td>
                            <td>
                                @if($alerta->tiempo_respuesta !== null)
                                    <span class="badge bg-light text-dark">{{ $alerta->tiempo_respuesta }}</span>
                                @else
                                    —
                                @endif
                            </td>
                            <td>
                                @if($alerta->ticket?->solucion_aplicada)
                                    <button class="btn btn-sm btn-link p-0" data-bs-toggle="tooltip"
                                            title="{{ $alerta->ticket->solucion_aplicada }}">
                                        <i class="bi bi-chat-left-text-fill text-success"></i>
                                    </button>
                                @else
                                    <span class="text-muted">—</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="9" class="text-center py-5 text-muted">
                                <i class="bi bi-inbox fs-1 d-block mb-2"></i>
                                No se encontraron registros
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

@push('scripts')
<script>
    // Activar tooltips de Bootstrap
    document.querySelectorAll('[data-bs-toggle="tooltip"]').forEach(el => {
        new bootstrap.Tooltip(el);
    });
</script>
@endpush