@extends('layouts.app')

@section('title', 'Panel de Alertas en Vivo')

@section('content')

{{-- Indicador de actualización automática --}}
<div id="update-indicator">
    <span class="badge bg-success" id="status-badge">
        <i class="bi bi-wifi me-1"></i> En vivo
    </span>
</div>

<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h4 class="mb-1">
            <i class="bi bi-bell-fill text-danger me-2"></i>
            Panel de Alertas en Vivo
        </h4>
        <small class="text-muted">
            Actualización automática cada 10 segundos ·
            Última actualización: <span id="ultima-actualizacion">ahora</span>
        </small>
    </div>
    <button class="btn btn-outline-secondary btn-sm" onclick="cargarAlertas()">
        <i class="bi bi-arrow-clockwise me-1"></i> Actualizar ahora
    </button>
</div>

{{-- ── ALERTAS PENDIENTES ── --}}
<div class="card shadow-sm mb-4">
    <div class="card-header bg-danger text-white d-flex justify-content-between align-items-center">
        <h6 class="mb-0">
            <i class="bi bi-exclamation-circle-fill me-2"></i>
            Alertas Pendientes
        </h6>
        <span class="badge bg-light text-danger" id="contador-pendientes">
            {{ $alertasPendientes->count() }}
        </span>
    </div>
    <div class="card-body p-0" id="lista-pendientes">
        @forelse($alertasPendientes as $alerta)
            @include('admin.partials.alerta-card', ['alerta' => $alerta, 'modo' => 'pendiente'])
        @empty
            <div class="text-center text-success py-5" id="sin-pendientes">
                <i class="bi bi-check-circle-fill fs-1 d-block mb-2"></i>
                <strong>Sin alertas pendientes</strong>
                <p class="text-muted small mb-0">Todo bajo control 👍</p>
            </div>
        @endforelse
    </div>
</div>

{{-- ── ALERTAS EN ATENCIÓN ── --}}
<div class="card shadow-sm">
    <div class="card-header bg-warning text-dark d-flex justify-content-between align-items-center">
        <h6 class="mb-0">
            <i class="bi bi-person-fill-gear me-2"></i>
            En Atención
        </h6>
        <span class="badge bg-dark text-warning" id="contador-atencion">
            {{ $alertasEnAtencion->count() }}
        </span>
    </div>
    <div class="card-body p-0" id="lista-atencion">
        @forelse($alertasEnAtencion as $alerta)
            @include('admin.partials.alerta-card', ['alerta' => $alerta, 'modo' => 'atencion'])
        @empty
            <div class="text-center text-muted py-4" id="sin-atencion">
                <p class="mb-0">Sin alertas en atención</p>
            </div>
        @endforelse
    </div>
</div>

{{-- Modal para agregar solución --}}
<div class="modal fade" id="modalSolucion" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="bi bi-check2-square me-2"></i>
                    Resolver Alerta
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <input type="hidden" id="alerta_id_resolver">
                <div class="mb-3">
                    <label class="form-label">Solución Aplicada (opcional)</label>
                    <textarea class="form-control" id="solucion_texto" rows="4"
                              placeholder="Describe brevemente qué se hizo para resolver la situación..."></textarea>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-success" onclick="resolverAlerta()">
                    <i class="bi bi-check-lg me-1"></i> Marcar como Resuelto
                </button>
            </div>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
    const CSRF = document.querySelector('meta[name="csrf-token"]').content;
    let pollingInterval = null;
    let alertasConocidas = new Set();

    /**
     * Llama al endpoint AJAX y actualiza las listas de alertas.
     */
    function cargarAlertas() {
        document.getElementById('status-badge').innerHTML = '<i class="bi bi-arrow-repeat me-1 spin"></i> Actualizando...';

        fetch('/api/alertas/pendientes', {
            headers: { 'Accept': 'application/json', 'X-CSRF-TOKEN': CSRF },
        })
        .then(r => r.json())
        .then(data => {
            actualizarListaAlertas(data.alertas);
            document.getElementById('ultima-actualizacion').textContent = new Date().toLocaleTimeString('es-CL');
            document.getElementById('status-badge').innerHTML = '<i class="bi bi-wifi me-1"></i> En vivo';
        })
        .catch(() => {
            document.getElementById('status-badge').innerHTML = '<i class="bi bi-wifi-off me-1"></i> Sin conexión';
            document.getElementById('status-badge').className = 'badge bg-danger';
        });
    }

    /**
     * Renderiza las alertas en el DOM.
     */
    function actualizarListaAlertas(alertas) {
        const pendientes = alertas.filter(a => a.estado === 'pendiente');
        const enAtencion = alertas.filter(a => a.estado === 'en_atencion');

        document.getElementById('contador-pendientes').textContent = pendientes.length;
        document.getElementById('contador-atencion').textContent  = enAtencion.length;

        // Detectar nuevas alertas para notificar
        alertas.forEach(a => {
            if (!alertasConocidas.has(a.id) && a.tipo === 'panico') {
                notificarPanico(a);
            }
            alertasConocidas.add(a.id);
        });

        // Renderizar lista de pendientes
        const listaPendientes = document.getElementById('lista-pendientes');
        if (pendientes.length === 0) {
            listaPendientes.innerHTML = `
                <div class="text-center text-success py-5">
                    <i class="bi bi-check-circle-fill fs-1 d-block mb-2"></i>
                    <strong>Sin alertas pendientes</strong>
                    <p class="text-muted small mb-0">Todo bajo control 👍</p>
                </div>`;
        } else {
            listaPendientes.innerHTML = pendientes.map(a => renderAlertaCard(a, 'pendiente')).join('');
        }

        // Renderizar lista en atención
        const listaAtencion = document.getElementById('lista-atencion');
        if (enAtencion.length === 0) {
            listaAtencion.innerHTML = `<div class="text-center text-muted py-4"><p class="mb-0">Sin alertas en atención</p></div>`;
        } else {
            listaAtencion.innerHTML = enAtencion.map(a => renderAlertaCard(a, 'atencion')).join('');
        }
    }

    /**
     * Genera el HTML de una tarjeta de alerta.
     */
    function renderAlertaCard(alerta, modo) {
        const esPanico  = alerta.tipo === 'panico';
        const colorBg   = esPanico ? 'danger' : alerta.tipo_color;

        let botones = '';
        if (modo === 'pendiente') {
            botones = `<button class="btn btn-sm btn-dark" onclick="atenderAlerta(${alerta.id})">
                            <i class="bi bi-hand-index-thumb me-1"></i> ATENDER
                       </button>`;
        } else {
            botones = `<button class="btn btn-sm btn-success" onclick="abrirModalSolucion(${alerta.id})">
                            <i class="bi bi-check-lg me-1"></i> Resolver
                       </button>`;
        }

        return `
            <div class="p-3 border-bottom alerta-card ${alerta.tipo} ${esPanico ? 'bg-danger bg-opacity-10' : ''}">
                <div class="row align-items-center">
                    <div class="col-md-3">
                        <span class="badge bg-${colorBg} fs-6 mb-1 d-block text-center p-2">
                            ${alerta.tipo_label}
                        </span>
                    </div>
                    <div class="col-md-6">
                        <strong><i class="bi bi-geo-alt me-1"></i>${alerta.sala}</strong>
                        <small class="text-muted d-block">${alerta.sala_codigo}</small>
                        <small class="text-muted">
                            <i class="bi bi-person me-1"></i>${alerta.usuario}
                            · <i class="bi bi-clock me-1"></i>${alerta.fecha_creacion}
                        </small>
                        ${alerta.mensaje ? `<div class="mt-1"><small class="text-body">"${alerta.mensaje}"</small></div>` : ''}
                    </div>
                    <div class="col-md-3 text-end">
                        <span class="badge bg-light text-dark d-block mb-2">
                            <i class="bi bi-stopwatch me-1"></i>${alerta.tiempo_transcurrido} min
                        </span>
                        ${botones}
                    </div>
                </div>
            </div>`;
    }

    /**
     * Notificación visual/sonora para alerta de pánico.
     */
    function notificarPanico(alerta) {
        Swal.fire({
            icon: 'error',
            title: '🚨 ALERTA DE PÁNICO',
            html: `<strong>Sala: ${alerta.sala}</strong><br>Enviado por: ${alerta.usuario}`,
            confirmButtonText: 'ENTENDIDO',
            confirmButtonColor: '#dc3545',
            timer: 15000,
            timerProgressBar: true,
        });
    }

    /**
     * Marca una alerta como "en atención".
     */
    function atenderAlerta(alertaId) {
        fetch(`/alerta/${alertaId}/atender`, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': CSRF, 'Accept': 'application/json' },
        })
        .then(r => r.json())
        .then(data => {
            if (data.success) {
                Swal.fire({ icon: 'success', title: 'En atención', text: data.message, timer: 2000, showConfirmButton: false });
                cargarAlertas();
            } else {
                Swal.fire({ icon: 'warning', title: 'Aviso', text: data.message });
            }
        });
    }

    /**
     * Abre el modal para ingresar solución.
     */
    function abrirModalSolucion(alertaId) {
        document.getElementById('alerta_id_resolver').value = alertaId;
        document.getElementById('solucion_texto').value     = '';
        new bootstrap.Modal(document.getElementById('modalSolucion')).show();
    }

    /**
     * Envía la solución y cierra la alerta.
     */
    function resolverAlerta() {
        const alertaId = document.getElementById('alerta_id_resolver').value;
        const solucion = document.getElementById('solucion_texto').value;

        fetch(`/alerta/${alertaId}/resolver`, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': CSRF, 'Accept': 'application/json' },
            body: JSON.stringify({ solucion_aplicada: solucion }),
        })
        .then(r => r.json())
        .then(data => {
            bootstrap.Modal.getInstance(document.getElementById('modalSolucion')).hide();
            if (data.success) {
                Swal.fire({ icon: 'success', title: '¡Resuelto!', text: data.message, timer: 2000, showConfirmButton: false });
                cargarAlertas();
            } else {
                Swal.fire({ icon: 'error', text: data.message });
            }
        });
    }

    // ── Inicializar polling automático ──
    cargarAlertas();
    pollingInterval = setInterval(cargarAlertas, 10000); // cada 10 segundos
</script>

<style>
    @keyframes spin { to { transform: rotate(360deg); } }
    .spin { display: inline-block; animation: spin 1s linear infinite; }
</style>
@endpush