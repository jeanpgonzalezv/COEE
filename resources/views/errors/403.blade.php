@extends('layouts.app')

@section('title', 'Acceso Denegado')

@section('content')
<div class="text-center py-5">
    <i class="bi bi-shield-lock-fill text-danger" style="font-size: 5rem;"></i>
    <h1 class="mt-3 text-danger">403</h1>
    <h4>Acceso Denegado</h4>
    <p class="text-muted">No tienes permisos para ver esta página.</p>
    <a href="{{ route('home') }}" class="btn btn-primary">
        <i class="bi bi-house me-2"></i>Volver al inicio
    </a>
</div>
@endsection