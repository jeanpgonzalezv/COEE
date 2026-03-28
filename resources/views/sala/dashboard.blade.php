@extends('layouts.app')

@section('title', 'Mi Panel — Sala')

@section('content')
<div class="row g-4">
    {{-- ── COLUMNA IZQUIERDA: Botones de alerta ── --}}
    <div class="col-lg-8">
        <div class="card shadow-sm">
            <div class="card-header bg-dark text-white">
                <h5 class="mb-0">
                    <i class="bi bi-broadcast me-2 text-danger"></i>
                    Enviar Alerta
                </h5>
                <small class="text-muted">Selecciona tu sala y el tipo de emergencia</small>
            </div>
            <div class="card-body p-4">

                {{-- Selector de sala --}}
                <div class="mb-4">
                    <label for="sala_select" class="form-label fw-bold">
                        <i class="bi bi-geo-alt me-1"></i> Mi Sala Actual
                    </label>
                    <select id="sala_select" class="form-select form-select-lg">
                        <option value="">-- Selecciona tu sala --</option>
                        @foreach($salas as $sala)
                            <option value="{{ $sala->id }}">
                                {{ $sala->nombre }} ({{ $sala->codigo }})
                            </option>
                        @endforeach
                    </select>
                </div>

                {{-- Botones de tipo de alerta --}}
                <div class="row g-3">
                    {{-- Enfermería --}}
                    <div class="col-6 col-md-4">
                        <button class="btn btn-success btn-alerta"
                                onclick="enviarAlerta('enfermeria')"
                                title="Solicitar Enfermería">
                            <i class="bi bi-heart-pulse-fill"></i>
                            Enfermería
                        </button>
                    </div>

                    {{-- Convivencia / PIE --}}
                    <div class="col-6 col-md-4">
                        <button class="btn btn-warning btn-alerta"
                                onclick="enviarAlerta('convivencia')"
                                title="Convivencia Escolar / PIE">
                            <i class="bi bi-people-fill"></i>
                            Convivencia / PIE
                        </button>
                    </div>

                    {{-- Soporte TI --}}
                    <div class="col-6 col-md-4">
                        <button class="btn btn-info btn-alerta"
                                onclick="enviarAlerta('soporte_ti')"
                                title="Soporte Técnico TI">
                            <i class="bi bi-laptop-fill"></i>
                            Soporte TI
                        </button>
                    </div>

                    {{-- UTP / Inspectoría --}}
                    <div class="col-6 col-md-6">
                        <button class="btn btn-primary btn-alerta"
                                onclick="enviarAlerta('utp')"
                                title="UTP / Inspectoría">
                            <i class="bi bi-clipboard2-check-fill"></i>
                            UTP / Inspectoría
                        </button>
                    </div>

                    {{-- PÁNICO (botón especial con confirmación) --}}
                    <div class="col-12 col-md-6">
                        <button class="btn btn-panico btn-alerta"
                                onclick="confirmarPanico()"
                                title="Botón de Pánico — Solo Emergencias">
                            <i class="bi bi-exclamation-triangle-fill"></i>
                            ⚠️ BOTÓN DE PÁNICO
                        </button>
                    </div>
                </div>

            </div>
        </div>
    </div>

    {{-- ── COLUMNA DERECHA: Alertas activas del profesor ── --}}
    <div class="col-lg-4">
        <div class="card shadow-sm">
            <div class="card-header bg-secondary text-white">
                <h6 class="mb-0">
                    <i class="bi bi-clock me-2"></i>
                    Mis Alertas Activas
                </h6>
            </div>
            <div class="card-body p-0">
                @forelse($alertasActivas as $alerta)
                    <div class="p-3 border-bottom alerta-card {{ $alerta->tipo }}">
                        <div class="d-flex justify-content-between align-items-start">
                            <span class="badge bg-{{ $alerta->tipo_color }} mb-1">
                                {{ $alerta->tipo_label }}
                            </span>
                            <span class="badge bg-light text-dark tiempo-badge">
                                <i class="bi bi-clock me-1"></i>
                                {{ $alerta->tiempo_transcurrido }} min
                            </span>
                        </div>
                        <div class="small text-muted">
                            <i class="bi bi-geo-alt me-1"></i>{{ $alerta->sala->nombre }}
                        </div>
                        <div class="mt-1">
                            <span class="badge bg-{{ $alerta->estado === 'pendiente' ? 'warning text-dark' : 'info' }}">
                                {{ $alerta->estado_label }}
                            </span>
                        </div>
                    </div>
                @empty
                    <div class="text-center text-muted py-4">
                        <i class="bi bi-check-circle-fill text-success fs-2 d-block mb-2"></i>
                        Sin alertas activas
                    </div>
                @endforelse
            </div>
        </div>

        {{-- Instrucciones --}}
        <div class="card mt-3 border-warning">
            <div class="card-body small">
                <h6 class="text-warning"><i class="bi bi-info-circle me-1"></i> Instrucciones</h6>
                <ol class="mb-0 ps-3">
                    <li>Selecciona tu sala actual</li>
                    <li>Presiona el botón correspondiente</li>
                    <li>El equipo recibirá tu alerta de inmediato</li>
                    <li>El botón de pánico es solo para emergencias graves</li>
                </ol>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    const CSRF_TOKEN = document.querySelector('meta[name="csrf-token"]').content;

    /**
     * Envía una alerta al servidor vía AJAX.
     */
    function enviarAlerta(tipo) {
        const salaId = document.getElementById('sala_select').value;

        if (!salaId) {
            Swal.fire({
                icon: 'warning',
                title: 'Selecciona tu sala',
                text: 'Debes indicar en qué sala estás antes de enviar una alerta.',
                confirmButtonColor: '#ffc107',
            });
            return;
        }

        fetch('/alerta/enviar', {
            method: 'POST',
            headers: {
                'Content-Type':  'application/json',
                'X-CSRF-TOKEN':  CSRF_TOKEN,
                'Accept':        'application/json',
            },
            body: JSON.stringify({ sala_id: salaId, tipo: tipo }),
        })
        .then(r => r.json())
        .then(data => {
            if (data.success) {
                Swal.fire({
                    icon: 'success',
                    title: '¡Alerta Enviada!',
                    text: data.message,
                    timer: 3000,
                    timerProgressBar: true,
                    showConfirmButton: false,
                });
                // Recargar la lista de alertas activas después de 1 segundo
                setTimeout(() => location.reload(), 3500);
            } else {
                Swal.fire({
                    icon: 'error',
                    title: 'No se pudo enviar',
                    text: data.message,
                });
            }
        })
        .catch(() => {
            Swal.fire({
                icon: 'error',
                title: 'Error de conexión',
                text: 'No se pudo conectar con el servidor. Verifica tu conexión.',
            });
        });
    }

    /**
     * Confirmación antes de enviar el botón de pánico.
     */
    function confirmarPanico() {
        const salaId = document.getElementById('sala_select').value;
        if (!salaId) {
            Swal.fire({ icon: 'warning', title: 'Selecciona tu sala', text: 'Debes indicar en qué sala estás.' });
            return;
        }

        Swal.fire({
            icon: 'warning',
            title: '⚠️ BOTÓN DE PÁNICO',
            html: '<strong>¿Confirmas que hay una emergencia grave?</strong><br><small class="text-muted">Esta acción notificará a todo el equipo directivo y administrativo de forma urgente.</small>',
            showCancelButton: true,
            confirmButtonText: 'SÍ, ES UNA EMERGENCIA',
            cancelButtonText: 'Cancelar',
            confirmButtonColor: '#dc3545',
            cancelButtonColor: '#6c757d',
            focusCancel: true,  // El foco por defecto es en "Cancelar" (más seguro)
        }).then(result => {
            if (result.isConfirmed) {
                enviarAlerta('panico');
            }
        });
    }
</script>
@endpush