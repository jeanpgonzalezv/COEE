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
   {{-- ── ASISTENTE EDUCATIVO INTEGRAL COEE ── --}}
@auth
<div id="chat-widget">

    {{-- Botón flotante --}}
    <button id="chat-toggle" onclick="toggleChat()" title="Asistente Educativo COEE">
        <i class="bi bi-mortarboard-fill" id="chat-icon-open"></i>
        <span id="chat-icon-close" style="display:none">✕</span>
    </button>

    {{-- Ventana del chat --}}
    <div id="chat-box" style="display:none">
        <div id="chat-header">
            <div>
                <strong>📚 Asistente Educativo COEE</strong>
                <small class="d-block" style="font-size:0.7rem;opacity:0.85">
                    Normativa y gestión escolar chilena
                </small>
            </div>
            <button onclick="toggleChat()" style="background:none;border:none;color:white;font-size:1.2rem;cursor:pointer">✕</button>
        </div>

        {{-- Chips de temas rápidos --}}
        <div id="chat-chips">
            <button class="chip" onclick="preguntaRapida('¿Qué debe contener el Manual de Convivencia Escolar?')">Manual Convivencia</button>
            <button class="chip" onclick="preguntaRapida('¿Qué exige MINEDUC en el Reglamento de Evaluación?')">Reglamento Evaluación</button>
            <button class="chip" onclick="preguntaRapida('¿Qué es el RICE y qué debe incluir?')">RICE</button>
            <button class="chip" onclick="preguntaRapida('¿Qué contiene un Protocolo de Accidentes Escolares?')">Accidentes</button>
            <button class="chip" onclick="preguntaRapida('¿Qué es el PISE y cómo se implementa?')">PISE</button>
            <button class="chip" onclick="preguntaRapida('¿Qué debe incluir un Protocolo de Salud Mental escolar?')">Salud Mental</button>
        </div>

        <div id="chat-messages">
            <div class="msg-bot">
                👋 Hola, soy el <strong>Asistente Educativo COEE</strong>.<br><br>
                Estoy especializado en normativa y gestión escolar chilena. Puedo orientarte sobre:<br>
                PEI · Manual de Convivencia · RICE · Protocolos de Salud Mental · PISE · NEE · y más.<br><br>
                <strong>¿En qué puedo ayudarte hoy?</strong>
            </div>
        </div>

        <div id="chat-input-area">
            <input type="text" id="chat-input"
                   placeholder="Ej: ¿Qué debe incluir el PEI?"
                   onkeypress="if(event.key==='Enter') enviarMensaje()">
            <button onclick="enviarMensaje()">
                <i class="bi bi-send-fill"></i>
            </button>
        </div>
    </div>
</div>

<style>
#chat-widget {
    position: fixed;
    bottom: 24px;
    right: 24px;
    z-index: 9999;
    font-family: Arial, sans-serif;
}
#chat-toggle {
    width: 58px;
    height: 58px;
    border-radius: 50%;
    background: linear-gradient(135deg, #198754, #0f5132);
    border: none;
    color: white;
    font-size: 1.5rem;
    cursor: pointer;
    box-shadow: 0 4px 15px rgba(0,0,0,0.3);
    transition: transform 0.2s;
    display: flex;
    align-items: center;
    justify-content: center;
}
#chat-toggle:hover { transform: scale(1.1); }
#chat-box {
    position: absolute;
    bottom: 70px;
    right: 0;
    width: 360px;
    background: white;
    border-radius: 16px;
    box-shadow: 0 8px 30px rgba(0,0,0,0.18);
    overflow: hidden;
    display: flex;
    flex-direction: column;
    max-height: 580px;
}
#chat-header {
    background: linear-gradient(135deg, #198754, #0f5132);
    color: white;
    padding: 14px 16px;
    display: flex;
    justify-content: space-between;
    align-items: center;
    flex-shrink: 0;
}
#chat-chips {
    padding: 8px 10px;
    background: #f8f9fa;
    border-bottom: 1px solid #dee2e6;
    display: flex;
    flex-wrap: wrap;
    gap: 5px;
    flex-shrink: 0;
}
.chip {
    background: #e8f5e9;
    color: #1b5e20;
    border: 1px solid #a5d6a7;
    border-radius: 20px;
    padding: 3px 10px;
    font-size: 0.75rem;
    cursor: pointer;
    transition: background 0.2s;
    font-weight: 500;
}
.chip:hover { background: #c8e6c9; }
#chat-messages {
    flex: 1;
    overflow-y: auto;
    padding: 14px;
    background: #f8f9fa;
    display: flex;
    flex-direction: column;
    gap: 10px;
    min-height: 200px;
}
.msg-user {
    background: #198754;
    color: white;
    padding: 10px 14px;
    border-radius: 14px 14px 4px 14px;
    align-self: flex-end;
    max-width: 82%;
    font-size: 0.88rem;
    line-height: 1.4;
}
.msg-bot {
    background: white;
    color: #1a1a2e;
    padding: 12px 14px;
    border-radius: 14px 14px 14px 4px;
    align-self: flex-start;
    max-width: 88%;
    font-size: 0.88rem;
    line-height: 1.55;
    box-shadow: 0 1px 4px rgba(0,0,0,0.08);
    border-left: 3px solid #198754;
}
.msg-typing {
    background: white;
    color: #6c757d;
    padding: 10px 14px;
    border-radius: 14px;
    font-size: 0.85rem;
    font-style: italic;
    align-self: flex-start;
}
#chat-input-area {
    display: flex;
    padding: 10px;
    border-top: 1px solid #dee2e6;
    background: white;
    gap: 8px;
    flex-shrink: 0;
}
#chat-input {
    flex: 1;
    border: 1px solid #dee2e6;
    border-radius: 20px;
    padding: 8px 14px;
    font-size: 0.85rem;
    outline: none;
}
#chat-input:focus { border-color: #198754; }
#chat-input-area button {
    background: #198754;
    color: white;
    border: none;
    border-radius: 50%;
    width: 36px;
    height: 36px;
    cursor: pointer;
    font-size: 0.95rem;
    transition: background 0.2s;
    display: flex;
    align-items: center;
    justify-content: center;
}
#chat-input-area button:hover { background: #157347; }
</style>

<script>
// ── BASE DE CONOCIMIENTO ──────────────────────────────────────────────────────
const BASE_CONOCIMIENTO = {

    // ── PEI ──────────────────────────────────────────────────────────────────
    pei: {
        keywords: ['pei','proyecto educativo','proyecto educativo institucional'],
        respuesta: `📘 <strong>Proyecto Educativo Institucional (PEI)</strong><br><br>
El PEI es el documento marco que define la identidad, visión, misión y valores del establecimiento.<br><br>
<strong>¿Qué debe contener según MINEDUC?</strong><br>
- Identidad institucional (misión, visión, sellos educativos)<br>
- Diagnóstico del contexto educativo<br>
- Objetivos estratégicos a largo plazo<br>
- Valores y principios que orientan la comunidad escolar<br>
- Compromisos con la mejora continua<br><br>
<strong>Base legal:</strong> Ley 20.370 (Ley General de Educación), Art. 46 letra f.<br><br>
💡 El PEI debe ser coherente con el Plan de Mejoramiento Educativo (PME) y revisarse periódicamente con la comunidad escolar.`
    },

    // ── MANUAL DE CONVIVENCIA ─────────────────────────────────────────────────
    manual_convivencia: {
        keywords: ['manual de convivencia','manual convivencia','convivencia escolar'],
        respuesta: `📗 <strong>Manual de Convivencia Escolar</strong><br><br>
El Manual de Convivencia regula las relaciones entre todos los integrantes de la comunidad educativa.<br><br>
<strong>Contenido mínimo exigido por MINEDUC:</strong><br>
- Derechos y deberes de estudiantes, familias y funcionarios<br>
- Normas de funcionamiento del establecimiento<br>
- Protocolos de actuación ante situaciones de conflicto<br>
- Medidas disciplinarias y procedimientos de apelación<br>
- Protocolo de acoso escolar (bullying)<br>
- Medidas de prevención de violencia<br>
- Canales de denuncia y comunicación<br><br>
<strong>Base legal:</strong> Ley 20.536 de Violencia Escolar y Política Nacional de Convivencia Escolar 2019-2022.<br><br>
💡 Debe elaborarse con participación del Consejo Escolar y actualizarse anualmente.`
    },

    // ── RICE ─────────────────────────────────────────────────────────────────
    rice: {
        keywords: ['rice','reglamento interno de convivencia','reglamento interno convivencia'],
        respuesta: `📙 <strong>Reglamento Interno de Convivencia Escolar (RICE)</strong><br><br>
El RICE es el instrumento normativo que establece las conductas esperadas y las medidas ante faltas.<br><br>
<strong>¿Qué debe incluir?</strong><br>
- Tipificación de faltas (leves, graves y gravísimas)<br>
- Procedimientos disciplinarios con garantías de debido proceso<br>
- Derecho a apelación del estudiante y su familia<br>
- Medidas formativas antes que punitivas<br>
- Protocolo de mediación escolar<br>
- Sanciones proporcionales a la gravedad de la falta<br>
- Plazos para cada procedimiento<br><br>
<strong>Base legal:</strong> Ley 20.536, Decreto 524 (Centro de Alumnos) y normativas MINEDUC.<br><br>
⚠️ El RICE no puede contener medidas que vulneren los derechos fundamentales del estudiante.`
    },

    // ── SALUD MENTAL ─────────────────────────────────────────────────────────
    salud_mental: {
        keywords: ['salud mental','protocolo salud mental','bienestar emocional','salud mental escolar'],
        respuesta: `💚 <strong>Protocolo de Salud Mental Escolar</strong><br><br>
MINEDUC exige a los establecimientos contar con protocolos para abordar situaciones de salud mental.<br><br>
<strong>Contenido esencial:</strong><br>
- Identificación temprana de señales de alerta<br>
- Ruta de derivación interna (orientador/psicólogo/dupla psicosocial)<br>
- Ruta de derivación externa (CESFAM, salud mental primaria)<br>
- Protocolo de crisis emocional en aula<br>
- Protocolo ante conducta suicida o autolesión<br>
- Acciones de prevención y promoción del bienestar<br>
- Coordinación con redes de apoyo externas<br><br>
<strong>Base legal:</strong> Política Nacional de Convivencia Escolar, Circular N°1 MINEDUC 2023 sobre salud mental.<br><br>
💡 Se recomienda la participación de la dupla psicosocial PIE en la elaboración y actualización del protocolo.`
    },

    // ── PISE ─────────────────────────────────────────────────────────────────
    pise: {
        keywords: ['pise','plan de inclusion','inclusion escolar','sensibilizacion escolar','plan inclusion'],
        respuesta: `🌈 <strong>Plan de Inclusión y Sensibilización Escolar (PISE)</strong><br><br>
El PISE orienta las acciones del establecimiento para promover una cultura inclusiva.<br><br>
<strong>Componentes principales:</strong><br>
- Diagnóstico de barreras para el aprendizaje y la participación<br>
- Objetivos anuales de inclusión<br>
- Acciones de sensibilización a la comunidad escolar<br>
- Estrategias de apoyo para estudiantes con NEE<br>
- Coordinación con el PIE (Programa de Integración Escolar)<br>
- Formación docente en educación inclusiva<br>
- Evaluación y seguimiento de avances<br><br>
<strong>Base legal:</strong> Decreto 83/2015 (DUA), Decreto 170/2009 (PIE), Ley 20.422 (Inclusión).<br><br>
💡 El DUA (Diseño Universal de Aprendizaje) debe ser el enfoque central del PISE.`
    },

    // ── REGLAMENTO DE EVALUACION ──────────────────────────────────────────────
    reglamento_evaluacion: {
        keywords: ['reglamento evaluacion','reglamento de evaluacion','evaluacion y promocion','decreto evaluacion','decreto 67'],
        respuesta: `📊 <strong>Reglamento de Evaluación y Promoción</strong><br><br>
Cada establecimiento debe tener su propio Reglamento de Evaluación conforme al Decreto 67/2018.<br><br>
<strong>Aspectos obligatorios:</strong><br>
- Escala de calificaciones (1.0 a 7.0)<br>
- Porcentaje mínimo de asistencia para ser promovido (85%)<br>
- Criterios de promoción por nivel<br>
- Procedimientos de evaluación diferenciada (NEE)<br>
- Tipos de evaluación: diagnóstica, formativa y sumativa<br>
- Derecho a apelación de calificaciones<br>
- Registro y comunicación de resultados<br>
- Adecuaciones curriculares y su evaluación<br><br>
<strong>Base legal:</strong> Decreto 67/2018 que reemplazó al Decreto 511.<br><br>
⚠️ El Decreto 67 enfatiza la evaluación formativa y prohíbe usar la calificación como sanción disciplinaria.`
    },

    // ── ACCIDENTES ESCOLARES ──────────────────────────────────────────────────
    accidentes: {
        keywords: ['accidente escolar','protocolo accidente','seguro escolar','accidentes escolares'],
        respuesta: `🏥 <strong>Protocolo de Accidentes Escolares</strong><br><br>
Chile cuenta con el Seguro Escolar (Ley 16.744) que cubre accidentes en el establecimiento y traslados.<br><br>
<strong>Pasos del protocolo:</strong><br>
1. Atención inmediata del estudiante (primeros auxilios básicos)<br>
2. Notificación a la familia o apoderado<br>
3. Traslado al servicio de salud más cercano si es necesario<br>
4. Completar el formulario DIAT (Denuncia Individual de Accidente del Trabajo)<br>
5. Registro en el Libro de Accidentes del establecimiento<br>
6. Seguimiento y alta médica<br>
7. Análisis del accidente para prevenir recurrencia<br><br>
<strong>Base legal:</strong> Ley 16.744, Circular N°2 MINEDUC sobre Seguro Escolar.<br><br>
💡 El sostenedor es responsable de tramitar el seguro escolar ante la ACHS, ISL u organismo administrador correspondiente.`
    },

    // ── NEE ──────────────────────────────────────────────────────────────────
    nee: {
        keywords: ['nee','necesidades educativas especiales','evaluacion diagnostica','reevaluacion','pie','programa de integracion'],
        respuesta: `♿ <strong>NEE y Programa de Integración Escolar (PIE)</strong><br><br>
Los procesos de evaluación y reevaluación de NEE son regulados por el Decreto 170/2009.<br><br>
<strong>Tipos de NEE:</strong><br>
- <strong>Transitorias:</strong> DEA, TEL, TDAH, Trastornos emocionales y conductuales<br>
- <strong>Permanentes:</strong> Discapacidad intelectual, visual, auditiva, motora, TEA, multidéficit<br><br>
<strong>Proceso de evaluación diagnóstica:</strong><br>
1. Derivación del docente al equipo PIE<br>
2. Evaluación psicopedagógica y psicológica<br>
3. Informe de evaluación con diagnóstico<br>
4. Plan de apoyo individual (PAI)<br>
5. Coordinación con la familia<br>
6. Reevaluación anual obligatoria<br><br>
<strong>Base legal:</strong> Decreto 170/2009, Decreto 83/2015, Ley 20.201.<br><br>
💡 El PIE financia horas de profesionales especialistas (educadores diferenciales, psicólogos, fonoaudiólogos).`
    },

    // ── CUENTA PUBLICA ────────────────────────────────────────────────────────
    cuenta_publica: {
        keywords: ['cuenta publica','cuenta pública','cuenta publica anual'],
        respuesta: `📢 <strong>Cuenta Pública Escolar</strong><br><br>
La Cuenta Pública es el mecanismo de transparencia y rendición de cuentas a la comunidad escolar.<br><br>
<strong>¿Qué debe incluir?</strong><br>
- Resultados académicos del año (SIMCE, PSU/PAES, notas)<br>
- Gestión de recursos financieros (subvenciones, fondos)<br>
- Avances del Plan de Mejoramiento Educativo (PME)<br>
- Matrícula y asistencia<br>
- Proyectos y actividades realizadas<br>
- Convivencia escolar: estadísticas e incidentes<br>
- Participación de la comunidad educativa<br><br>
<strong>Base legal:</strong> Ley 20.529 (SEP), Ley 20.845 de Inclusión.<br><br>
💡 Debe realizarse al menos una vez al año, con participación del Consejo Escolar y debe ser pública y accesible para toda la comunidad.`
    },

    // ── ENTREGA DE ESTUDIANTES ────────────────────────────────────────────────
    entrega_estudiantes: {
        keywords: ['entrega de estudiantes','protocolo entrega','retiro de estudiantes','retiro anticipado'],
        respuesta: `🚶 <strong>Protocolo de Entrega de Estudiantes</strong><br><br>
Este protocolo regula el retiro anticipado y la entrega de estudiantes a sus apoderados.<br><br>
<strong>Elementos esenciales:</strong><br>
- Registro de personas autorizadas para retirar al estudiante<br>
- Verificación de identidad con cédula de identidad<br>
- Registro escrito del retiro (hora, motivo, persona que retira)<br>
- Procedimiento ante ausencia del apoderado titular<br>
- Protocolo para estudiantes con medidas de protección judicial<br>
- Actuación ante intentos de retiro no autorizados<br>
- Coordinación con Carabineros si es necesario<br><br>
⚠️ <strong>Situaciones especiales:</strong> En caso de padres separados con medidas judiciales, el establecimiento debe respetar estrictamente las resoluciones del tribunal de familia.<br><br>
💡 Se recomienda mantener actualizada la ficha del estudiante con datos de contacto y personas autorizadas.`
    },

    // ── COMPROMISO ESCOLAR ────────────────────────────────────────────────────
    compromiso_escolar: {
        keywords: ['compromiso escolar','corresponsabilidad','familia escuela','participacion familia'],
        respuesta: `🤝 <strong>Compromiso Escolar y Corresponsabilidad Familia-Escuela</strong><br><br>
La corresponsabilidad implica que familia y escuela comparten la responsabilidad en la educación.<br><br>
<strong>Instrumentos clave:</strong><br>
- Libreta de comunicaciones<br>
- Entrevistas y reuniones de apoderados<br>
- Compromisos escritos de la familia<br>
- Consejo Escolar con participación de apoderados<br>
- Centro General de Padres y Apoderados (CGPA)<br><br>
<strong>Buenas prácticas:</strong><br>
- Establecer canales de comunicación bidireccional<br>
- Informar oportunamente sobre el progreso del estudiante<br>
- Involucrar a las familias en la construcción del PEI<br>
- Respetar la diversidad familiar<br><br>
<strong>Base legal:</strong> Ley 20.370 Art. 4, Política de Participación de Familias MINEDUC 2017.<br><br>
💡 La participación familiar es un factor protector clave para el bienestar y rendimiento escolar.`
    },

    // ── PROTOCOLO DE EVALUACION DIAGNOSTICA ──────────────────────────────────
    evaluacion_diagnostica: {
        keywords: ['evaluacion diagnostica','evaluación diagnóstica','diagnostico inicial','evaluacion inicial'],
        respuesta: `🔍 <strong>Protocolo de Evaluación Diagnóstica</strong><br><br>
La evaluación diagnóstica permite identificar los saberes previos y necesidades de los estudiantes al inicio de cada año o unidad.<br><br>
<strong>Propósitos:</strong><br>
- Identificar aprendizajes previos<br>
- Detectar brechas y necesidades de apoyo<br>
- Planificar la enseñanza con base en evidencia<br>
- Identificar posibles NEE de forma temprana<br><br>
<strong>Etapas del protocolo:</strong><br>
1. Diseño de instrumentos diagnósticos (no calificados)<br>
2. Aplicación en las primeras semanas del año escolar<br>
3. Análisis de resultados por docente y equipo PIE<br>
4. Planificación de acciones remediales<br>
5. Comunicación a familias de los resultados<br><br>
<strong>Base legal:</strong> Decreto 67/2018 (evaluación formativa), Decreto 83/2015 (DUA).<br><br>
💡 Los resultados diagnósticos NO deben incidir en la calificación del estudiante según el Decreto 67.`
    },

    // ── RESPUESTA GENERICA ────────────────────────────────────────────────────
    default: {
        respuesta: `🤔 Entiendo tu consulta. Como Asistente Educativo COEE puedo orientarte en:<br><br>
<strong>Documentos y Protocolos:</strong><br>
- PEI · Manual de Convivencia · RICE<br>
- Protocolos de Salud Mental · Accidentes Escolares<br>
- PISE · Entrega de Estudiantes<br><br>
<strong>Normativa y Evaluación:</strong><br>
- Reglamento de Evaluación (Decreto 67)<br>
- Evaluación Diagnóstica · NEE y PIE<br><br>
<strong>Gestión Escolar:</strong><br>
- Cuenta Pública · Compromiso Familiar<br><br>
Por favor reformula tu pregunta siendo más específico o usa los botones de acceso rápido. ¿Sobre qué tema necesitas orientación?`
    }
};

// ── FUNCIONES DEL CHAT ────────────────────────────────────────────────────────
function toggleChat() {
    const box   = document.getElementById('chat-box');
    const open  = document.getElementById('chat-icon-open');
    const close = document.getElementById('chat-icon-close');
    const visible = box.style.display !== 'none';
    box.style.display   = visible ? 'none' : 'flex';
    box.style.flexDirection = 'column';
    open.style.display  = visible ? 'inline-block' : 'none';
    close.style.display = visible ? 'none' : 'inline';
    if (!visible) document.getElementById('chat-input').focus();
}

function preguntaRapida(texto) {
    document.getElementById('chat-input').value = texto;
    enviarMensaje();
}

function enviarMensaje() {
    const input = document.getElementById('chat-input');
    const texto = input.value.trim();
    if (!texto) return;

    agregarMensaje(texto, 'user');
    input.value = '';

    // Indicador de escritura
    const typing = document.createElement('div');
    typing.className = 'msg-typing';
    typing.id = 'typing-indicator';
    typing.textContent = '📚 Consultando base de conocimiento...';
    document.getElementById('chat-messages').appendChild(typing);
    scrollChat();

    // Simular delay de respuesta
    setTimeout(() => {
        document.getElementById('typing-indicator')?.remove();
        const respuesta = buscarRespuesta(texto.toLowerCase());
        agregarMensaje(respuesta, 'bot');
    }, 800);
}

function buscarRespuesta(query) {
    // Buscar en la base de conocimiento por keywords
    for (const [key, data] of Object.entries(BASE_CONOCIMIENTO)) {
        if (key === 'default') continue;
        if (data.keywords && data.keywords.some(kw => query.includes(kw))) {
            return data.respuesta;
        }
    }
    return BASE_CONOCIMIENTO.default.respuesta;
}

function agregarMensaje(texto, tipo) {
    const div = document.createElement('div');
    div.className = tipo === 'user' ? 'msg-user' : 'msg-bot';
    if (tipo === 'bot') {
        div.innerHTML = texto;
    } else {
        div.textContent = texto;
    }
    document.getElementById('chat-messages').appendChild(div);
    scrollChat();
}

function scrollChat() {
    const msgs = document.getElementById('chat-messages');
    msgs.scrollTop = msgs.scrollHeight;
}
</script>
@endauth


</body>
</html>