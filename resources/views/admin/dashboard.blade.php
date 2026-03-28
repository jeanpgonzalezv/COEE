@extends('layouts.app')

@section('title', 'Dashboard Directivo')

@section('content')

{{-- ── TARJETAS RESUMEN ── --}}
<div class="row g-3 mb-4">
    <div class="col-md-3">
        <div class="card bg-primary text-white shadow-sm">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <div class="fs-2 fw-bold">{{ $totalAlertas }}</div>
                        <div class="small">Total Alertas (30 días)</div>
                    </div>
                    <i class="bi bi-bell-fill fs-1 opacity-50"></i>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card bg-success text-white shadow-sm">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <div class="fs-2 fw-bold">{{ $totalResueltas }}</div>
                        <div class="small">Alertas Resueltas</div>
                    </div>
                    <i class="bi bi-check-circle-fill fs-1 opacity-50"></i>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card bg-warning text-dark shadow-sm">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <div class="fs-2 fw-bold">{{ $totalPendientes }}</div>
                        <div class="small">Pendientes Hoy</div>
                    </div>
                    <i class="bi bi-hourglass-split fs-1 opacity-50"></i>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card bg-info text-white shadow-sm">
            <div class="card-body">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <div class="fs-2 fw-bold">{{ $tiempoPromedio }} min</div>
                        <div class="small">Tiempo Prom. Respuesta</div>
                    </div>
                    <i class="bi bi-stopwatch-fill fs-1 opacity-50"></i>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- ── GRÁFICOS ── --}}
<div class="row g-4 mb-4">
    {{-- Gráfico: alertas por tipo --}}
    <div class="col-md-6">
        <div class="card shadow-sm">
            <div class="card-header">
                <h6 class="mb-0"><i class="bi bi-bar-chart-fill me-2"></i>Alertas por Tipo (30 días)</h6>
            </div>
            <div class="card-body">
                <canvas id="graficoTipo" height="200"></canvas>
            </div>
        </div>
    </div>

    {{-- Gráfico: alertas por sala --}}
    <div class="col-md-6">
        <div class="card shadow-sm">
            <div class="card-header">
                <h6 class="mb-0"><i class="bi bi-building me-2"></i>Top Salas con más Alertas</h6>
            </div>
            <div class="card-body">
                <canvas id="graficoSala" height="200"></canvas>
            </div>
        </div>
    </div>

    {{-- Gráfico: tendencia por día --}}
    <div class="col-12">
        <div class="card shadow-sm">
            <div class="card-header">
                <h6 class="mb-0"><i class="bi bi-graph-up me-2"></i>Tendencia de Alertas (últimos 30 días)</h6>
            </div>
            <div class="card-body">
                <canvas id="graficoTendencia" height="100"></canvas>
            </div>
        </div>
    </div>
</div>

{{-- ── EXPORTAR REPORTE ── --}}
<div class="card shadow-sm mb-4">
    <div class="card-header">
        <h6 class="mb-0"><i class="bi bi-file-earmark-spreadsheet me-2"></i>Exportar Reporte a CSV</h6>
    </div>
    <div class="card-body">
        <form action="{{ route('reportes.exportar-csv') }}" method="GET" class="row g-3 align-items-end">
            @csrf
            <div class="col-md-4">
                <label class="form-label">Fecha Inicio</label>
                <input type="date" name="fecha_inicio" class="form-control"
                       value="{{ now()->subDays(30)->format('Y-m-d') }}" required>
            </div>
            <div class="col-md-4">
                <label class="form-label">Fecha Fin</label>
                <input type="date" name="fecha_fin" class="form-control"
                       value="{{ now()->format('Y-m-d') }}" required>
            </div>
            <div class="col-md-4">
                <button type="submit" class="btn btn-success w-100">
                    <i class="bi bi-download me-2"></i>Descargar CSV
                </button>
            </div>
        </form>
    </div>
</div>

@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<script>
    // ─── Datos desde Laravel (PHP → JS) ──────────────────────────────
    const alertasPorTipo = @json($alertasPorTipo);
    const alertasPorSala = @json($alertasPorSala);
    const diasLabels     = @json($diasLabels);
    const diasData       = @json($diasData);

    // ─── Paleta de colores por tipo ──────────────────────────────────
    const coloresTipo = {
        enfermeria:  '#198754',
        convivencia: '#ffc107',
        soporte_ti:  '#0dcaf0',
        utp:         '#0d6efd',
        panico:      '#dc3545',
    };

    const labelesTipo = {
        enfermeria:  'Enfermería',
        convivencia: 'Convivencia / PIE',
        soporte_ti:  'Soporte TI',
        utp:         'UTP / Inspectoría',
        panico:      'Pánico',
    };

    // ─── Gráfico 1: Alertas por tipo ─────────────────────────────────
    new Chart(document.getElementById('graficoTipo'), {
        type: 'bar',
        data: {
            labels: Object.keys(alertasPorTipo).map(k => labelesTipo[k] || k),
            datasets: [{
                label: 'Alertas',
                data:  Object.values(alertasPorTipo),
                backgroundColor: Object.keys(alertasPorTipo).map(k => coloresTipo[k] || '#6c757d'),
                borderRadius: 6,
            }],
        },
        options: {
            responsive: true,
            plugins: { legend: { display: false } },
            scales:  { y: { beginAtZero: true, ticks: { stepSize: 1 } } },
        },
    });

    // ─── Gráfico 2: Alertas por sala ─────────────────────────────────
    new Chart(document.getElementById('graficoSala'), {
        type: 'bar',
        data: {
            labels:   alertasPorSala.map(s => s.sala),
            datasets: [{
                label: 'Alertas',
                data:  alertasPorSala.map(s => s.total),
                backgroundColor: '#0d6efd',
                borderRadius: 6,
            }],
        },
        options: {
            indexAxis: 'y',   // Barras horizontales
            responsive: true,
            plugins: { legend: { display: false } },
            scales:  { x: { beginAtZero: true, ticks: { stepSize: 1 } } },
        },
    });

    // ─── Gráfico 3: Tendencia por día ────────────────────────────────
    new Chart(document.getElementById('graficoTendencia'), {
        type: 'line',
        data: {
            labels:   diasLabels,
            datasets: [{
                label:           'Alertas por día',
                data:            diasData,
                borderColor:     '#dc3545',
                backgroundColor: 'rgba(220,53,69,0.1)',
                fill:            true,
                tension:         0.4,
                pointRadius:     3,
            }],
        },
        options: {
            responsive: true,
            plugins: { legend: { display: false } },
            scales:  { y: { beginAtZero: true, ticks: { stepSize: 1 } } },
        },
    });
</script>
@endpush