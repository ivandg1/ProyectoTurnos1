@extends('layouts.app')

@section('content')
<div class="row">
    <div class="col-md-12">
        <div class="card card-custom">
            <div class="card-header bg-white d-flex justify-content-between align-items-center">
                <h4 class="mb-0">Vista Previa de Rotación de Turnos</h4>
                <a href="{{ url('/turnos') }}" class="btn btn-secondary">
                    ← Volver a Grilla de Turnos
                </a>
            </div>
            <div class="card-body">
                <!-- Contenido temporal vacío -->
                <div class="text-center py-5">
                    <div class="mb-4">
                        <svg width="100" height="100" viewBox="0 0 24 24" fill="none" stroke="#667eea" stroke-width="1" stroke-linecap="round" stroke-linejoin="round">
                            <path d="M2 3h6a4 4 0 0 1 4 4v14a3 3 0 0 0-3-3H2z"></path>
                            <path d="M22 3h-6a4 4 0 0 0-4 4v14a3 3 0 0 1 3-3h7z"></path>
                        </svg>
                    </div>
                    <h3 class="text-muted">Vista Previa de Rotación</h3>
                    <p class="text-muted">Próximamente: Visualización de la rotación de turnos</p>
                    <p class="text-muted small">Aquí se mostrará la rotación de turnos según los parámetros seleccionados</p>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection