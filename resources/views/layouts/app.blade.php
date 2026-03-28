<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>COEE - @yield('title', 'Central de Operaciones y Emergencias Escolares')</title>

    {{-- Bootstrap 5 --}}
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css">
    {{-- Bootstrap Icons --}}
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    {{-- SweetAlert2 --}}
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">

    <style>
        :root {
            --color-panico:      #dc3545;
            --color-enfermeria:  #198754;
            --color-convivencia: #ffc107;
            --color-soporte-ti:  #0dcaf0;
            --color-utp:         #0d6efd;
        }

        body { background-color: #f8f9fa; }

        /* Navbar COEE */
        .navbar-coee {
            background: linear-gradient(135deg, #1a1a2e 0%, #16213e 100%);
            box-shadow: 0 2px 10px rgba(0,0,0,0.3);
        }

        /* Badge de rol */
        .badge-rol {
            font-size: 0.7rem;
            letter-spacing: 0.05em;
            text-transform: uppercase;
        }

        /* Tarjetas de alerta */
        .alerta-card {
            transition: transform 0.2s, box-shadow 0.2s;
            border-left: 5px solid;
        }

        .alerta-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(0,0,0,0.12);
        }

        .alerta-card.panico      { border-left-color: var(--color-panico); }
        .alerta-card.enfermeria  { border-left-color: var(--color-enfermeria); }
        .alerta-card.convivencia { border-left-color: var(--color-convivencia); }
        .alerta-card.soporte_ti  { border-left-color: var(--color-soporte-ti); }
        .alerta-card.utp         { border-left-color: var(--color-utp); }

        /* Botones de alerta del profesor */
        .btn-alerta {
            width: 100%;
            height: 120px;
            font-size: 1.1rem;
            font-weight: 600;
            border-radius: 12px;
            transition: transform 0.15s, box-shadow 0.15s;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            gap: 8px;
        }

        .btn-alerta:hover {
            transform: scale(1.04);
            box-shadow: 0 8px 25px rgba(0,0,0,0.2);
        }

        .btn-alerta .bi {
            font-size: 2rem;
        }

        .btn-panico {
            background: linear-gradient(135deg, #dc3545 0%, #b02a37 100%);
            border: none;
            color: white;
            animation: pulso-panico 2s infinite;
        }

        @keyframes pulso-panico {
            0%, 100% { box-shadow: 0 0 0 0 rgba(220,53,69,0.4); }
            50%       { box-shadow: 0 0 0 15px rgba(220,53,69,0); }
        }

        /* Indicador de tiempo */
        .tiempo-badge {
            font-size: 0.75rem;
            padding: 3px 8px;
        }

        /* Actualización automática — indicador */
        #update-indicator {
            position: fixed;
            bottom: 20px;
            right: 20px;
            z-index: 9999;
        }
    </style>

    @stack('styles')
</head>
<body>

    {{-- ── NAVBAR ── --}}
    <nav class="navbar navbar-expand-lg navbar-dark navbar-coee">
        <div class="container-fluid">
            <a class="navbar-brand fw-bold" href="{{ route('home') }}">
                <i class="bi bi-shield-fill-exclamation text-danger me-2"></i>
                COEE
                <small class="text-muted fs-6 fw-normal d-none d-md-inline"> | Central de Operaciones</small>
            </a>

            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navMenu">
                <span class="navbar-toggler-icon"></span>
            </button>

            <div class="collapse navbar-collapse" id="navMenu">
                <ul class="navbar-nav me-auto">
                    @auth
                        @if(auth()->user()->esProfesor())
                            <li class="nav-item">
                                <a class="nav-link" href="{{ route('sala.dashboard') }}">
                                    <i class="bi bi-grid-3x2-gap me-1"></i> Mi Panel
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" href="{{ route('sala.historial') }}">
                                    <i class="bi bi-clock-history me-1"></i> Historial
                                </a>
                            </li>
                        @elseif(auth()->user()->esAdministrativo())
                            <li class="nav-item">
                                <a class="nav-link" href="{{ route('admin.panel') }}">
                                    <i class="bi bi-bell-fill me-1"></i> Panel de Alertas
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" href="{{ route('admin.historial') }}">
                                    <i class="bi bi-journal-text me-1"></i> Historial
                                </a>
                            </li>
                        @elseif(auth()->user()->esDirectivo())
                            <li class="nav-item">
                                <a class="nav-link" href="{{ route('admin.dashboard') }}">
                                    <i class="bi bi-bar-chart-fill me-1"></i> Dashboard
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" href="{{ route('admin.panel') }}">
                                    <i class="bi bi-bell-fill me-1"></i> Alertas en Vivo
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link" href="{{ route('admin.historial') }}">
                                    <i class="bi bi-journal-text me-1"></i> Historial
                                </a>
                            </li>
                        @endif
                    @endauth
                </ul>

                <ul class="navbar-nav ms-auto align-items-center">
                    @auth
                        <li class="nav-item me-3">
                            <span class="text-light">
                                <i class="bi bi-person-circle me-1"></i>
                                {{ auth()->user()->name }}
                                <span class="badge bg-secondary badge-rol ms-1">
                                    {{ auth()->user()->rol_label }}
                                </span>
                            </span>
                        </li>
                        <li class="nav-item">
                            <form method="POST" action="{{ route('logout') }}" class="d-inline">
                                @csrf
                                <button type="submit" class="btn btn-outline-light btn-sm">
                                    <i class="bi bi-box-arrow-right me-1"></i> Salir
                                </button>
                            </form>
                        </li>
                    @endauth
                </ul>
            </div>
        </div>
    </nav>

    {{-- ── CONTENIDO PRINCIPAL ── --}}
    <main class="py-4">
        <div class="container-fluid">

            {{-- Mensajes Flash de Laravel --}}
            @if(session('success'))
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <i class="bi bi-check-circle-fill me-2"></i>{{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif

            @if(session('error'))
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <i class="bi bi-exclamation-triangle-fill me-2"></i>{{ session('error') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            @endif

            @yield('content')
        </div>
    </main>

    {{-- ── FOOTER ── --}}
    <footer class="text-center text-muted py-3 mt-5 border-top">
        <small>COEE &copy; {{ date('Y') }} — Sistema de Comunicación Escolar Interna</small>
    </footer>

    {{-- ── SCRIPTS ── --}}
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.all.min.js"></script>

    @stack('scripts')
</body>
</html>