@extends('layouts.app')

@section('content')
<div class="row">   
    <div class="col-md-12">
        <div class="card card-custom">
            <div class="card-header bg-white d-flex justify-content-between align-items-center flex-wrap">
                <div>
                    <h4 class="mb-0">📅 Vista Previa de Rotación de Turnos</h4>
                    <small class="text-muted" id="infoRotacion">Cargando información...</small>
                </div>
                <div class="mt-2 mt-md-0">
                    <a href="{{ url('/turnos') }}" class="btn btn-secondary">
                        ← Volver a Grilla de Turnos
                    </a>
                </div>
            </div>
            <div class="card-body">
                <!-- Barra de progreso -->
                <div id="loadingContainer" class="text-center py-5">
                    <div class="spinner-border text-primary mb-3" role="status" style="width: 3rem; height: 3rem;">
                        <span class="visually-hidden">Cargando...</span>
                    </div>
                    <h5 class="text-muted">Generando calendario de rotación...</h5>
                    <p class="text-muted">Esto puede tomar unos segundos</p>
                    <div class="progress mt-3" style="height: 10px; max-width: 300px; margin: 0 auto;">
                        <div class="progress-bar progress-bar-striped progress-bar-animated" role="progressbar" style="width: 100%"></div>
                    </div>
                </div>

                <!-- Contenido del calendario -->
                <div id="calendarioContent" style="display: none;">
                    <!-- Controles de navegación por semanas -->
                    <div class="d-flex justify-content-between align-items-center mb-3 flex-wrap">
                        <div class="btn-group mb-2 mb-md-0" id="semanaButtons">
                            <!-- Botones de semanas se generan dinámicamente -->
                        </div>
                        <div>
                            <button type="button" class="btn btn-sm btn-outline-success" id="btnExportar">
                                📊 Exportar
                            </button>
                            <button type="button" class="btn btn-sm btn-outline-info" id="btnImprimir">
                                🖨️ Imprimir
                            </button>
                        </div>
                    </div>

                    <!-- Tabla de calendario (scroll horizontal) -->
                    <div class="table-responsive" style="max-height: 70vh; overflow-y: auto;">
                        <table class="table table-bordered table-hover table-sm" id="calendarioTable" style="min-width: 800px;">
                            <thead class="table-dark">
                                <!-- Cabecera se genera dinámicamente -->
                            </thead>
                            <tbody id="calendarioBody">
                                <!-- Cuerpo se genera dinámicamente -->
                            </tbody>
                        </table>
                    </div>
                    
                    <!-- Resumen -->
                    <div class="alert alert-info mt-3" id="resumenCalendario">
                        <small>Cargando resumen...</small>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
const BASE_URL = window.location.origin + '/ProyectoTurnos1/public';
let semanas = {{ $semanas }};
let rotacionData = null;
let totalTurnos = 0; // ✅ Agregar esta línea si es necesario

$(document).ready(function() {
    cargarDatosRotacion();
});

function cargarDatosRotacion() {
    mostrarLoading();
    
    $.ajax({
        url: BASE_URL + '/api/rotacion-data',
        method: 'GET',
        data: { semanas: semanas },
        success: function(response) {
            if (response.success) {
                rotacionData = response;
                generarCalendarioConDatos();
            } else {
                mostrarError(response.message || 'Error al cargar datos');
            }
        },
        error: function(xhr) {
            console.error('Error:', xhr);
            mostrarError('Error al cargar los datos de rotación');
        }
    });
}

function mostrarLoading() {
    $('#loadingContainer').show();
    $('#calendarioContent').hide();
}

function ocultarLoading() {
    $('#loadingContainer').hide();
    $('#calendarioContent').show();
}

function mostrarError(mensaje) {
    $('#loadingContainer').hide();
    $('#calendarioContent').html(`<div class="alert alert-danger">${mensaje}</div>`).show();
}

function generarCalendarioConDatos() {
    if (!rotacionData || !rotacionData.workers || rotacionData.workers.length === 0) {
        $('#calendarioBody').html(`
            <tr>
                <td colspan="${rotacionData?.fechas?.length + 1 || 2}" class="text-center text-muted py-5">
                    <div class="mb-3">
                        <svg width="60" height="60" viewBox="0 0 24 24" fill="none" stroke="#6c757d" stroke-width="1.5">
                            <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path>
                            <circle cx="12" cy="7" r="4"></circle>
                        </svg>
                    </div>
                    <h6>No hay trabajadores cargados</h6>
                    <p class="small">Los trabajadores se mostrarán aquí después de generar la rotación</p>
                </td>
            </tr>
        `);
        ocultarLoading();
        return;
    }
    
    const fechas = rotacionData.fechas;
    const workers = rotacionData.workers;
    const totalTurnosData = rotacionData.total_turnos || 0; // ✅ Declarar aquí
    
    // Actualizar información
    const fechaInicio = fechas[0]?.fecha || '';
    const fechaFin = fechas[fechas.length - 1]?.fecha || '';
    $('#infoRotacion').text(`${workers.length} trabajadores | ${semanas} semana(s) | ${fechas.length} días | ${totalTurnosData} turnos asignados`);
    
    // Generar cabecera
    generarCabecera(fechas);
    
    // Generar cuerpo con trabajadores y turnos
    generarCuerpo(workers, fechas);
    
    // Actualizar resumen
    $('#resumenCalendario').html(`
        <small>
            📅 <strong>Período de rotación:</strong> Del ${fechaInicio} al ${fechaFin}<br>
            👥 <strong>Trabajadores:</strong> ${workers.length} | 📊 <strong>Total turnos asignados:</strong> ${totalTurnosData} | 🔄 <strong>Rotación:</strong> Arriba → Abajo
        </small>
    `);
    
    // Generar botones de navegación
    generarBotonesSemanas(fechas);
    
    ocultarLoading();
}

function generarCabecera(fechas) {
    let theadHtml = '<tr><th style="min-width: 180px; position: sticky; left: 0; background-color: #212529; z-index: 1;">Trabajador</th>';
    
    fechas.forEach(fecha => {
        theadHtml += `<th style="min-width: 100px; text-align: center;">
                        ${fecha.dia_semana}<br>
                        <small>${fecha.dia_numero}</small>
                      </th>`;
    });
    
    $('#calendarioTable thead').html(theadHtml);
}

function generarCuerpo(workers, fechas) {
    let tbodyHtml = '';
    
    workers.forEach(worker => {
             
        let filaHtml = `<tr>
            <td class="fw-bold" style="position: sticky; left: 0; background-color: white; z-index: 1;">
                ${escapeHtml(worker.nombre)}<br>
                <small class="text-muted">${worker.rut || ''}</small>
            </td>`;
        
        fechas.forEach((fecha, index) => {
            const semana = Math.floor(index / 7);
            const turnoData = worker.turnos[fecha.fecha];
            const turno = turnoData?.turno;
            
            if (turno) {
                const horasTurno = parseFloat(turno.horas_trabajadas) || 0;
                
                filaHtml += `<td class="text-center" style="background-color: #e8f5e9;">
                                <small><strong>${escapeHtml(turno.nombre?.substring(0, 15) || '—')}</strong></small><br>
                                <small>${turno.hora_entrada || '—'}-${turno.hora_salida || '—'}</small>
                                <br><small class="text-muted">${horasTurno.toFixed(1)} hrs</small>
                             </td>`;
            } else {
                filaHtml += `<td class="text-center text-muted">—</td>`;
            }
        });
        
        filaHtml += '</tr>';
        
        tbodyHtml += filaHtml;
    });
    
    $('#calendarioBody').html(tbodyHtml);
}

function generarBotonesSemanas(fechas) {
    const totalSemanas = Math.ceil(fechas.length / 7);
    let html = '<div class="btn-group" role="group">';
    
    for (let i = 1; i <= totalSemanas; i++) {
        const fechaInicioSemana = fechas[(i - 1) * 7];
        const fechaFinSemana = fechas[Math.min(i * 7 - 1, fechas.length - 1)];
        const textoSemana = `Sem ${i} (${fechaInicioSemana?.dia_numero || ''} - ${fechaFinSemana?.dia_numero || ''})`;
        
        html += `<button type="button" class="btn btn-sm btn-outline-primary semana-btn" data-semana="${i}">${textoSemana}</button>`;
    }
    
    html += '</div>';
    $('#semanaButtons').html(html);
    
    // Evento para scroll a la semana seleccionada
    $('.semana-btn').on('click', function() {
        const semana = $(this).data('semana');
        const columnaInicio = (semana - 1) * 7 + 1;
        
        const tabla = document.getElementById('calendarioTable');
        const container = tabla?.parentElement;
        
        if (container && tabla) {
            const celdas = tabla.querySelectorAll('th');
            if (celdas[columnaInicio]) {
                const scrollPosicion = celdas[columnaInicio].offsetLeft - 20;
                container.scrollLeft = scrollPosicion;
            }
        }
        
        $('.semana-btn').removeClass('btn-primary').addClass('btn-outline-primary');
        $(this).removeClass('btn-outline-primary').addClass('btn-primary');
    });
    
    // Seleccionar primera semana por defecto
    $('.semana-btn:first').click();
}

function escapeHtml(text) {
    if (!text) return '';
    return text.replace(/[&<>]/g, function(m) {
        if (m === '&') return '&amp;';
        if (m === '<') return '&lt;';
        if (m === '>') return '&gt;';
        return m;
    });
}

// Función para exportar (placeholder)
$('#btnExportar').on('click', function() {
    showToast('Funcionalidad de exportación próximamente', 'info');
});

// Función para imprimir
$('#btnImprimir').on('click', function() {
    window.print();
});

function showToast(message, type) {
    if (!$('#toast-container').length) {
        $('body').append('<div id="toast-container" style="position:fixed;top:20px;right:20px;z-index:9999"></div>');
    }
    
    const bgColor = type === 'success' ? 'bg-success' : (type === 'info' ? 'bg-info' : 'bg-danger');
    const icon = type === 'success' ? '✓' : (type === 'info' ? 'ℹ' : '✗');
    
    const toast = $(`<div class="toast align-items-center text-white ${bgColor} border-0 mb-2" data-bs-autohide="true" data-bs-delay="3000">
        <div class="d-flex">
            <div class="toast-body">${icon} ${message}</div>
            <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
        </div>
    </div>`);
    
    $('#toast-container').append(toast);
    const bsToast = new bootstrap.Toast(toast);
    bsToast.show();
    
    toast.on('hidden.bs.toast', () => toast.remove());
}
</script>
@endpush
@endsection