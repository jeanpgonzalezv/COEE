# COEE — Central de Operaciones y Emergencias Escolares

Sistema web de comunicación interna para establecimientos educacionales, que permite a los profesores enviar alertas de emergencia desde su sala con un clic, y al personal administrativo recibirlas y gestionarlas en tiempo real.

Proyecto desarrollado para la asignatura **TPY1101 — Taller Aplicado de Programación**, carrera **Analista Programador**, **DUOC UC**.

---

## 📋 Tabla de contenidos

- [Problema que resuelve](#-problema-que-resuelve)
- [Equipo de desarrollo](#-equipo-de-desarrollo)
- [Stack tecnológico](#-stack-tecnológico)
- [Arquitectura](#-arquitectura)
- [Roles de usuario](#-roles-de-usuario)
- [Instalación local con Laragon](#-instalación-local-con-laragon)
- [Credenciales de prueba](#-credenciales-de-prueba)
- [Estructura del proyecto](#-estructura-del-proyecto)
- [Funcionalidades principales](#-funcionalidades-principales)
- [Comandos útiles](#-comandos-útiles)
- [Respaldo de base de datos](#-respaldo-de-base-de-datos)
- [Estado del proyecto](#-estado-del-proyecto)

---

## 🎯 Problema que resuelve

En los establecimientos educacionales chilenos, la comunicación interna ante emergencias se realiza de forma verbal o telefónica. Esto genera:

- El profesor debe abandonar la sala para buscar ayuda
- No existe registro formal de los incidentes
- El tiempo de respuesta ante emergencias es elevado
- Los directivos no tienen visibilidad de la frecuencia de incidentes

**COEE** resuelve esto permitiendo el envío de alertas con un clic, recepción en tiempo real, trazabilidad completa y estadísticas para la dirección.

---

## 👥 Equipo de desarrollo

| Integrante | Rol SCRUM | Rol Técnico |
|---|---|---|
| Andrea Andrade  | Product Owner | Representante del cliente |
| Yordan Cisterna | Scrum Master  | Full Stack Developer |
| Nicolás Tamayo  | Developer     | Full Stack Developer |
| Jean González   | Developer     |Full Stack Developer |
| Yordan Cisterna | Developer     |Full Stack Developer |

---

## 🛠 Stack tecnológico

| Capa | Tecnología | Versión |
|---|---|---|
| Backend | PHP | 8.3.30 |
| Framework | Laravel | 13.2.0 |
| Base de datos | MySQL / MariaDB | 8.4.3 |
| Frontend | Bootstrap | 5.3.2 |
| Templates | Blade | 13.x |
| Gráficos | Chart.js | 4.4.0 |
| Alertas UI | SweetAlert2 | 11.x |
| Autenticación | Laravel Breeze | 13.x |
| Asistente | Chatbot local en JavaScript (sin API key) |
| Entorno dev | Laragon | 6.x |
| Control de versiones | Git / GitHub | — |

---

## 🏗 Arquitectura

COEE utiliza una **arquitectura monolítica con patrón MVC**. Todo el sistema —frontend, backend y base de datos— vive en una única aplicación Laravel.

```
┌──────────────────────────────────────────────────────┐
│  CAPA 1 — Navegadores Web                             │
│  Profesor · Inspector/Admin · Director/UTP · Login    │
└───────────────────────┬────────────────────────────────┘
                         ▼
┌──────────────────────────────────────────────────────┐
│  CAPA 2 — Laravel 13 (MVC)                            │
│  Middleware Auth → Router → Controllers → Models      │
│  Polling AJAX (GET /api/alertas/pendientes cada 10s)  │
└───────────────────────┬────────────────────────────────┘
                         ▼
┌──────────────────────────────────────────────────────┐
│  CAPA 3 — MySQL / MariaDB                             │
│  users · salas · alertas · tickets · reportes         │
└──────────────────────────────────────────────────────┘
```

La comunicación en tiempo real se simula mediante **polling AJAX cada 10 segundos**, evitando dependencias de pago como Pusher o configuraciones complejas de WebSockets.

> Diagramas completos de arquitectura y modelo entidad-relación disponibles en `/docs`.

---

## 🔐 Roles de usuario

| Rol | Acceso a |
|---|---|
| `profesor` | Panel de envío de alertas (`/sala/dashboard`) |
| `inspector` | Panel de atención de alertas (`/admin/panel`) |
| `enfermeria` | Panel de atención de alertas (`/admin/panel`) |
| `soporte_ti` | Panel de atención de alertas (`/admin/panel`) |
| `utp` | Panel de atención de alertas (`/admin/panel`) |
| `director` | Dashboard con estadísticas (`/admin/dashboard`) |

Cada ruta está protegida por el middleware `CheckRole`, que verifica el rol del usuario autenticado antes de permitir el acceso.

---

## 💻 Instalación local con Laragon

### Requisitos previos
- [Laragon](https://laragon.org/) instalado (incluye PHP, MySQL, Composer y Node.js)
- Git

### Pasos

```bash
# 1. Clonar el repositorio
cd C:/laragon/www
git clone https://github.com/jeanpgonzalezv/COEE.git coee
cd coee

# 2. Instalar dependencias PHP
composer install

# 3. Instalar dependencias Node y compilar assets
npm install
npm run build

# 4. Configurar el entorno
cp .env.example .env
php artisan key:generate
```

### Configurar la base de datos

Edita el archivo `.env` con tus datos de Laragon:

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=coee_db
DB_USERNAME=root
DB_PASSWORD=
```

Crea la base de datos `coee_db` desde HeidiSQL (botón **Database** en Laragon) y luego ejecuta:

```bash
php artisan migrate:fresh --seed
```

### Levantar el servidor

```bash
php artisan serve
```

Accede en tu navegador a **http://localhost:8000**

---

## 🔑 Credenciales de prueba

Todos los usuarios generados por el seeder usan la contraseña: **`password`**

| Rol | Email |
|---|---|
| Director | `director@coee.cl` |
| UTP | `utp@coee.cl` |
| Inspector | `inspector@coee.cl` |
| Enfermería | `enfermeria@coee.cl` |
| Soporte TI | `soporte@coee.cl` |
| Profesor 1 | `profesor1@coee.cl` |
| Profesor 2 | `profesor2@coee.cl` |
| Profesor 3 | `profesor3@coee.cl` |

> ⚠️ Para probar el flujo completo (profesor envía → inspector recibe) usa **dos navegadores distintos** (ej: Chrome y Edge), ya que las sesiones de Laravel pueden mezclarse si usas pestañas del mismo navegador.

---

## 📁 Estructura del proyecto

```
coee/
├── app/
│   ├── Http/
│   │   ├── Controllers/
│   │   │   ├── AlertaController.php      # enviar, atender, resolver alertas
│   │   │   ├── PanelController.php       # vistas sala / admin / dashboard
│   │   │   └── ReporteController.php     # exportación CSV
│   │   └── Middleware/
│   │       └── CheckRole.php             # protección de rutas por rol
│   └── Models/
│       ├── User.php
│       ├── Sala.php
│       ├── Alerta.php
│       ├── Ticket.php
│       └── Reporte.php
├── database/
│   ├── migrations/                       # 5 migraciones principales
│   └── seeders/                          # usuarios, salas y alertas de prueba
├── resources/
│   └── views/
│       ├── layouts/app.blade.php         # layout base + chatbot local
│       ├── sala/                         # vistas del profesor
│       └── admin/                        # vistas administrativas
└── routes/
    └── web.php                           # rutas agrupadas por rol
```

---

## ⚙️ Funcionalidades principales

### Envío de alertas (Profesor)
- Selección de sala activa
- 5 tipos de alerta: Enfermería, Convivencia/PIE, Soporte TI, UTP, Pánico
- Confirmación visual con SweetAlert2
- Modal de confirmación obligatoria para alerta de pánico
- Validación de alertas duplicadas

### Panel administrativo en tiempo real
- Actualización automática cada 10 segundos (polling AJAX)
- Separación de alertas pendientes y en atención
- Botones para atender y resolver con registro de solución

### Dashboard del Director
- Gráficos con Chart.js (barras, horizontal, tendencia)
- Tarjetas de resumen estadístico
- Exportación de reportes en CSV (compatible con Excel)

### Asistente Educativo Integral
- Chatbot local en JavaScript, **sin API key ni conexión a internet**
- Base de conocimiento sobre normativa escolar chilena: PEI, Manual de Convivencia, RICE, Protocolos de Salud Mental, PISE, Reglamento de Evaluación (Decreto 67), Accidentes Escolares, NEE/PIE (Decreto 170), Cuenta Pública, Entrega de Estudiantes, Compromiso Escolar y Evaluación Diagnóstica
- Chips de preguntas rápidas y búsqueda por palabras clave

---

## 🧰 Comandos útiles

| Comando | Descripción |
|---|---|
| `php artisan serve` | Levanta el servidor de desarrollo |
| `php artisan migrate:fresh --seed` | Resetea la BD y carga datos de prueba |
| `php artisan config:clear` | Limpia caché de configuración (tras editar `.env`) |
| `php artisan route:list` | Lista todas las rutas registradas |
| `npm run build` | Compila assets de frontend |

---

## 💾 Respaldo de base de datos

### Método rápido — HeidiSQL
1. Abrir HeidiSQL desde Laragon (botón **Database**)
2. Clic derecho sobre `coee_db` → **Exportar** → **Exportar base de datos como SQL**
3. Marcar **Datos** y **Estructura**, guardar el archivo `.sql`

### Método terminal — mysqldump
```bash
mysqldump -u root -p coee_db > C:/respaldos/coee_backup.sql
```

### Restauración
```bash
mysql -u root -p coee_db < C:/respaldos/coee_backup.sql
```

> 📄 Procedimiento completo documentado en `/docs/COEE_Procedimiento_Respaldo_BD.docx`

---

## 📊 Estado del proyecto

| Sprint | Contenido | Estado |
|---|---|---|
| Sprint 0 | Planificación e inicio | ✅ Completado |
| Sprint 1 | Autenticación y estructura base | ✅ Completado |
| Sprint 2 | Gestión de alertas y panel en tiempo real | ✅ Completado |
| Sprint 3 | Tickets, dashboard y reportes | ✅ Completado |
| Sprint 4 | Pruebas integrales y entrega final | ✅ Completado |

Gestión del proyecto mediante metodología **SCRUM** con tablero **Jira**.
(https://duocuc-team-mzbgc5lp.atlassian.net/jira/software/projects/COEE/boards/34?atlOrigin=eyJpIjoiNjEyZDg3OGIxM2Q2NDcyNDgzY2M5ZWIxODU2MmFiNGMiLCJwIjoiaiJ9)
---

## 📄 Licencia

Proyecto académico desarrollado con fines educativos para DUOC UC. Uso libre para fines de aprendizaje.

---

## 📬 Contacto

Repositorio: [github.com/jeanpgonzalezv/COEE](https://github.com/jeanpgonzalezv/COEE)
