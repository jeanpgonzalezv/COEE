<?php

use App\Http\Controllers\AlertaController;
use App\Http\Controllers\PanelController;
use App\Http\Controllers\ReporteController;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;


// ── Página de inicio: redirigir según rol ──────────────────────────────────
Route::get('/', function () {
    if (!auth()->check()) {
        return redirect()->route('login');
    }

    $user = auth()->user();

    if ($user->esProfesor()) {
        return redirect()->route('sala.dashboard');
    } elseif ($user->esAdministrativo()) {
        return redirect()->route('admin.panel');
    } elseif ($user->esDirectivo()) {
        return redirect()->route('admin.dashboard');
    }

    return redirect()->route('login');
})->name('home');

// ── Rutas de autenticación (generadas por Breeze) ─────────────────────────
require __DIR__.'/auth.php';

// ── Rutas del Profesor ────────────────────────────────────────────────────
Route::middleware(['auth', 'role:profesor'])
    ->prefix('sala')
    ->name('sala.')
    ->group(function () {
        Route::get('/dashboard', [PanelController::class, 'salaProfesor'])->name('dashboard');
        Route::get('/historial', [AlertaController::class, 'historialProfesor'])->name('historial');
    });

// ── Rutas de Alertas (envío desde profesor) ───────────────────────────────
Route::middleware(['auth', 'role:profesor'])
    ->group(function () {
        Route::post('/alerta/enviar', [AlertaController::class, 'enviar'])->name('alerta.enviar');
    });

// ── Rutas de Gestión de Alertas (administrativos y directivos) ────────────
Route::middleware(['auth', 'role:inspector,enfermeria,soporte_ti,utp,director'])
    ->group(function () {
        Route::post('/alerta/{alerta}/atender', [AlertaController::class, 'atender'])->name('alerta.atender');
        Route::post('/alerta/{alerta}/resolver', [AlertaController::class, 'resolver'])->name('alerta.resolver');
        Route::post('/alerta/{alerta}/cerrar',   [AlertaController::class, 'cerrar'])->name('alerta.cerrar');
    });

// ── API para polling (panel administrativo) ───────────────────────────────
Route::middleware(['auth', 'role:inspector,enfermeria,soporte_ti,utp,director'])
    ->prefix('api')
    ->group(function () {
        Route::get('/alertas/pendientes', [AlertaController::class, 'pendientes'])->name('api.alertas.pendientes');
    });

// ── Panel Administrativo ──────────────────────────────────────────────────
Route::middleware(['auth', 'role:inspector,enfermeria,soporte_ti,utp,director'])
    ->prefix('admin')
    ->name('admin.')
    ->group(function () {
        Route::get('/panel',    [PanelController::class, 'panelAdministrativo'])->name('panel');
        Route::get('/historial', [PanelController::class, 'historial'])->name('historial');
    });

// ── Dashboard Directivo (Director y UTP) ─────────────────────────────────
Route::middleware(['auth', 'role:director,utp'])
    ->prefix('admin')
    ->name('admin.')
    ->group(function () {
        Route::get('/dashboard', [PanelController::class, 'dashboardDirector'])->name('dashboard');
    });

// ── Reportes y Exportación ────────────────────────────────────────────────
Route::middleware(['auth', 'role:director,utp'])
    ->prefix('admin')
    ->name('reportes.')
    ->group(function () {
        Route::get('/exportar-csv', [ReporteController::class, 'exportarCsv'])->name('exportar-csv');
    });


