{{--
  Vista parcial para una tarjeta de alerta.
  Variables: $alerta (Alerta), $modo ('pendiente' | 'atencion')
--}}
<div class="p-3 border-bottom alerta-card {{ $alerta->tipo }} {{ $alerta->tipo === 'panico' ? 'bg-danger bg-opacity-10' : '' }}">
    <div class="row align-items-center">
        <div class="col-md-3">
            <span class="badge bg-{{ $alerta->tipo_color }} fs-6 mb-1 d-block text-center p-2">
                {{ $alerta->tipo_label }}
            </span>
        </div>
        <div class="col-md-6">
            <strong><i class="bi bi-geo-alt me-1"></i>{{ $alerta->sala->nombre }}</strong>
            <small class="text-muted d-block">{{ $alerta->sala->codigo }}</small>
            <small class="text-muted">
                <i class="bi bi-person me-1"></i>{{ $alerta->usuario->name }}
                · <i class="bi bi-clock me-1"></i>{{ $alerta->fecha_creacion->format('H:i') }}
            </small>
            @if($alerta->mensaje)
                <div class="mt-1">
                    <small class="text-body">"{{ $alerta->mensaje }}"</small>
                </div>
            @endif
        </div>
        <div class="col-md-3 text-end">
            <span class="badge bg-light text-dark d-block mb-2">
                <i class="bi bi-stopwatch me-1"></i>{{ $alerta->tiempo_transcurrido }} min
            </span>

            @if($modo === 'pendiente')
                <button class="btn btn-sm btn-dark"
                        onclick="atenderAlerta({{ $alerta->id }})">
                    <i class="bi bi-hand-index-thumb me-1"></i> ATENDER
                </button>
            @else
                <button class="btn btn-sm btn-success"
                        onclick="abrirModalSolucion({{ $alerta->id }})">
                    <i class="bi bi-check-lg me-1"></i> Resolver
                </button>
            @endif
        </div>
    </div>
</div>