@extends('layouts.app')

<style>
    #removeShiftBtn:enabled {
        cursor: pointer !important;
    }
    #removeShiftBtn:disabled {
        cursor: not-allowed !important;
        opacity: 0.5;
    }
</style>

@section('content')
<div class="row">
    <div class="col-md-12">
        <div class="card card-custom">
            <div class="card-header bg-white d-flex justify-content-between align-items-center">
                <div>
                    <h4 class="mb-0">Grilla de Turnos Rotativos</h4>
                    <small class="text-muted" id="totalTrabajadoresGrilla">Cargando...</small>
                </div>
                <div>
                    <button type="button" class="btn btn-info me-2" data-bs-toggle="modal" data-bs-target="#viewShiftsModal">
                        Mantenedor de Turnos
                    </button>
                    <a href="{{ url('/preview-rotacion') }}" class="btn btn-warning me-2">
                        🔄 Vista Previa Rotación
                    </a>
                    <a href="{{ url('/') }}" class="btn btn-secondary">
                        ← Volver a Trabajadores
                    </a>
                </div>
            </div>
            <div class="card-body">

                <div class="card-body py-3">
                    <div class="row align-items-end">
                        <div class="col-md-4 mb-2 mb-md-0">
                            <label for="semanasRotacion" class="form-label small fw-bold">📅 Cantidad de Semanas</label>
                            <input type="number" id="semanasRotacion" class="form-control form-control-sm" 
                                value="4" min="1" step="1" style="font-family: monospace;">
                        </div>
                        <div class="col-md-4 mb-2 mb-md-0">
                            <label for="sentidoRotacion" class="form-label small fw-bold">🔄 Sentido de la Rotación</label>
                            <select id="sentidoRotacion" class="form-select form-select-sm">
                                <option value="abajo">De arriba hacia abajo</option>
                                <option value="arriba">De abajo hacia arriba</option>
                            </select>
                        </div>
                        <div class="col-md-4">
                            
                        </div>
                    </div>
                </div>

                <!-- Barra de paginación (opcional, para muchos trabajadores) -->
                <div class="row mb-3">
                    <div class="col-md-6">
                        <label>Mostrar:</label>
                        <select id="perPage" class="form-select form-select-sm d-inline-block w-auto">
                            <option value="10">10 trabajadores</option>
                            <option value="25" selected>25 trabajadores</option>
                            <option value="50">50 trabajadores</option>
                            <option value="100">100 trabajadores</option>
                        </select>
                    </div>
                    <div class="col-md-6 text-end">
                        <input type="text" id="searchWorker" class="form-control form-control-sm d-inline-block w-auto" 
                               placeholder="Buscar trabajador...">
                    </div>
                </div>

                <div class="table-responsive">
                    <table class="table table-bordered table-hover">
                        <thead class="table-dark">
                            <tr>
                                <th style="width: 150px;">Trabajador</th>
                                <th style="width: 100px;">Lunes</th>
                                <th style="width: 100px;">Martes</th>
                                <th style="width: 100px;">Miércoles</th>
                                <th style="width: 100px;">Jueves</th>
                                <th style="width: 100px;">Viernes</th>
                                <th style="width: 100px;">Sábado</th>
                                <th style="width: 100px;">Domingo</th>
                                <th style="width: 100px;">Horas</th>
                            </tr>
                        </thead>
                        <tbody id="turnosGrid">
                            <!-- Los datos se cargan vía AJAX -->
                            <tr><td colspan="9" class="text-center">Cargando...</td><tr>
                        </tbody>
                    </table>
                </div>
                
                <!-- Paginación -->
                <div id="pagination" class="mt-3"></div>
            </div>
        </div>
    </div>
</div>

<!-- Modal para asignar turno -->
<div class="modal fade" id="assignShiftModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white;">
                <h5 class="modal-title">Asignar Turno</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p><strong>Trabajador:</strong> <span id="modalWorkerName"></span></p>
                <p><strong>Día:</strong> <span id="modalDay"></span></p>
                <div class="mb-3">
                    <label for="turnoSelect" class="form-label">Seleccionar Turno</label>
                    <select id="turnoSelect" class="form-select">
                        <option value="">-- Sin turno --</option>
                        <!-- Opciones cargadas vía AJAX -->
                    </select>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-danger" id="removeShiftBtn">🗑️ Quitar Turno</button>
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-custom" id="saveShiftBtn">Asignar Turno</button>
            </div>
        </div>
    </div>
</div>

<!-- Modal para Mantenedor de Turnos -->
<div class="modal fade" id="viewShiftsModal" tabindex="-1" data-bs-backdrop="static">
    <div class="modal-dialog modal-xl">
        <div class="modal-content">
            <div class="modal-header" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white;">
                <h5 class="modal-title">Mantenedor de Turnos Diarios</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <!-- Formulario para agregar/editar -->
                <div class="card mb-4">
                    <div class="card-header bg-light">
                        <h6 class="mb-0" id="formTitle">Nuevo Turno</h6>
                    </div>
                    <div class="card-body">
                        <form id="turnoForm">
                            <input type="hidden" id="turno_id" name="turno_id">
                            <div class="row">
                                <div class="col-md-4 mb-3">
                                    <label for="nombre" class="form-label">Nombre del Turno *</label>
                                    <input type="text" class="form-control" id="nombre" name="nombre" required>
                                    <div class="invalid-feedback"></div>
                                </div>
                                <div class="col-md-3 mb-3">
                                    <label for="hora_entrada" class="form-label">Hora Entrada *</label>
                                    <input type="text" class="form-control" id="hora_entrada" name="hora_entrada" 
                                        placeholder="HH:MM" maxlength="5" style="font-family: monospace;" 
                                        oninput="this.value = this.value.replace(/[^0-9:]/g, '').slice(0,5)" 
                                        required>
                                </div>
                                <div class="col-md-3 mb-3">
                                    <label for="hora_salida" class="form-label">Hora Salida *</label>
                                    <input type="text" class="form-control" id="hora_salida" name="hora_salida" 
                                        placeholder="HH:MM" maxlength="5" style="font-family: monospace;"
                                        oninput="this.value = this.value.replace(/[^0-9:]/g, '').slice(0,5)"
                                        required>
                                    <div class="invalid-feedback"></div>
                                </div>
                                <div class="col-md-2 mb-3">
                                    <label class="form-label">Horas Trabajadas</label>
                                    <input type="text" class="form-control" id="horas_trabajadas_display" readonly>
                                    <small class="text-muted">Se descuenta 1 hora de colación</small>
                                </div>
                            </div>
                            <button type="submit" class="btn btn-custom" id="saveTurnoBtn">Guardar Turno</button>
                            <button type="button" class="btn btn-secondary" id="cancelEditBtn" style="display:none;">Cancelar Edición</button>
                        </form>
                    </div>
                </div>

                <!-- Tabla de turnos existentes -->
                <div class="table-responsive">
                    <table class="table table-hover table-striped">
                        <thead class="table-dark">
                            <tr><th>ID</th><th>Nombre</th><th>Entrada</th><th>Salida</th><th>Horas</th><th>Acciones</th></tr>
                        </thead>
                        <tbody id="turnosTable">
                            <tr><td colspan="6" class="text-center">Cargando...</td></tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
const BASE_URL = window.location.origin + '/ProyectoTurnos1/public';
let currentPage = 1;
let perPage = 25;
let searchTerm = '';
let tipoTurnos = [];
let currentAssign = { workerId: null, workerName: null, day: null, dayIndex: null };

// ============================================
// FUNCIONES DEL MANTENEDOR DE TURNOS
// ============================================

// Calcular horas trabajadas (versión corregida)
function calcularHorasMantenedor() {
    const horaEntrada = $('#hora_entrada').val();
    const horaSalida = $('#hora_salida').val();
        
    if (!horaEntrada || !horaSalida) {
        $('#horas_trabajadas_display').val('');
        return false;
    }
    
    // Convertir horas a minutos
    function timeToMinutes(time) {
        const parts = time.split(':');
        return parseInt(parts[0]) * 60 + parseInt(parts[1]);
    }
    
    let entradaMinutos = timeToMinutes(horaEntrada);
    let salidaMinutos = timeToMinutes(horaSalida);
    
    // Si la salida es menor que la entrada, asumir que es del día siguiente
    if (salidaMinutos < entradaMinutos) {
        salidaMinutos += 24 * 60;
    }
    
    const diferenciaMinutos = salidaMinutos - entradaMinutos;
    const diferenciaHoras = diferenciaMinutos / 60;
    
    // Restar 1 hora de colación
    const horasTrabajadas = diferenciaHoras - 1;
    
    
    if (horasTrabajadas > 0) {
        $('#horas_trabajadas_display').val(horasTrabajadas.toFixed(1) + ' horas');
        $('#horas_trabajadas_display').css('color', 'green');
        return true;
    } else {
        $('#horas_trabajadas_display').val('❌ Mínimo 2 horas totales');
        $('#horas_trabajadas_display').css('color', 'red');
        return false;
    }
}

// Cargar turnos en el mantenedor
function cargarTurnosMantenedor() {
    
    $.ajax({
        url: BASE_URL + '/api/tipos-turno',
        method: 'GET',
        success: function(turnos) {
            
            if (!turnos || turnos.length === 0) {
                $('#turnosTable').html('<tr><td colspan="6" class="text-center">No hay turnos registrados</td></tr>');
            } else {
                let html = '';
                turnos.forEach(turno => {
                    html += `
                        <tr id="turno-row-${turno.id}">
                            <td>${turno.id}</td>
                            <td>${escapeHtml(turno.nombre)}</td>
                            <td>${turno.hora_entrada}</td>
                            <td>${turno.hora_salida}</td>
                            <td>${turno.horas_trabajadas} horas</td>
                            <td>
                                <button class="btn btn-warning btn-sm edit-turno-btn" data-id="${turno.id}">✏️ Editar</button>
                                <button class="btn btn-danger btn-sm delete-turno-btn" data-id="${turno.id}">🗑️ Eliminar</button>
                            </td>
                        </tr>
                    `;
                });
                $('#turnosTable').html(html);
            }
        },
        error: function(xhr) {
            console.error('Error al cargar turnos:', xhr);
            $('#turnosTable').html('<td><td colspan="6" class="text-center text-danger">Error al cargar turnos</td></tr>');
        }
    });
}

// Resetear formulario del mantenedor
function resetFormMantenedor() {
    $('#turnoForm')[0].reset();
    $('#turno_id').val('');
    $('#formTitle').text('Nuevo Turno');
    $('#cancelEditBtn').hide();
    $('.is-invalid').removeClass('is-invalid');
    $('.invalid-feedback').text('');
    $('#horas_trabajadas_display').val('');
}

// Guardar turno (crear o actualizar)
// Guardar turno (crear o actualizar)
function guardarTurnoMantenedor(event) {
    event.preventDefault();
    
    // Limpiar errores
    $('.is-invalid').removeClass('is-invalid');
    $('.invalid-feedback').text('');
    
    const nombre = $('#nombre').val().trim();
    let horaEntrada = $('#hora_entrada').val().trim();
    let horaSalida = $('#hora_salida').val().trim();
    
    // Validaciones básicas
    let hasError = false;
    if (!nombre) {
        $('#nombre').addClass('is-invalid');
        $('#nombre').siblings('.invalid-feedback').text('El nombre es obligatorio');
        hasError = true;
    }
    if (!horaEntrada) {
        $('#hora_entrada').addClass('is-invalid');
        $('#hora_entrada').siblings('.invalid-feedback').text('La hora de entrada es obligatoria');
        hasError = true;
    }
    if (!horaSalida) {
        $('#hora_salida').addClass('is-invalid');
        $('#hora_salida').siblings('.invalid-feedback').text('La hora de salida es obligatoria');
        hasError = true;
    }
    
    if (hasError) return;
    
    // Convertir a formato 24h si es necesario
    function convertirHora24(horaStr) {
        if (/^([0-1][0-9]|2[0-3]):[0-5][0-9]$/.test(horaStr)) {
            return horaStr;
        }
        const match = horaStr.match(/(\d{1,2}):(\d{2})\s*(AM|PM|am|pm)/i);
        if (match) {
            let horas = parseInt(match[1]);
            const minutos = match[2];
            const periodo = match[3].toUpperCase();
            if (periodo === 'PM' && horas < 12) horas += 12;
            if (periodo === 'AM' && horas === 12) horas = 0;
            return `${horas.toString().padStart(2, '0')}:${minutos}`;
        }
        return horaStr;
    }
    
    horaEntrada = convertirHora24(horaEntrada);
    horaSalida = convertirHora24(horaSalida);
    
    // Validar horas calculadas
    const horasValidas = calcularHorasMantenedor();
    if (!horasValidas) {
        showToast('El turno debe durar al menos 2 horas totales (1 hora de trabajo + 1 de colación)', 'error');
        return;
    }
    
    const turnoId = $('#turno_id').val();
    const esEdicion = turnoId && turnoId !== '';
    const url = esEdicion ? BASE_URL + '/api/tipos-turno/' + turnoId : BASE_URL + '/api/tipos-turno';
    const method = esEdicion ? 'PUT' : 'POST';
    
    const submitBtn = $('#saveTurnoBtn');
    const originalText = submitBtn.text();
    submitBtn.text('Guardando...').prop('disabled', true);
    
    // Guardar el turno ID original para usarlo después
    const turnoIdOriginal = turnoId;
    
    $.ajax({
        url: url,
        method: method,
        data: {
            nombre: nombre,
            hora_entrada: horaEntrada,
            hora_salida: horaSalida,
            _token: $('meta[name="csrf-token"]').attr('content')
        },
        dataType: 'json',
        success: function(response) {
            if (response.success) {
                // Obtener el ID del turno (puede ser nuevo o el existente)
                const turnoActualizado = response.turno;
                const turnoIdActualizado = turnoActualizado.id;
                
                // Calcular horas trabajadas actualizadas
                const horasTrabajadasActualizadas = turnoActualizado.horas_trabajadas;
                
                // Si es edición, actualizar todas las asignaciones locales que usan este turno
                if (esEdicion && turnoIdOriginal) {
                    actualizarAsignacionesPorTurno(turnoIdOriginal, {
                        id: turnoIdActualizado,
                        nombre: nombre,
                        hora_entrada: horaEntrada,
                        hora_salida: horaSalida,
                        horas_trabajadas: horasTrabajadasActualizadas
                    });
                }
                
                showToast(response.message, 'success');
                resetFormMantenedor();
                cargarTurnosMantenedor(); // Recargar la tabla del mantenedor
                cargarTiposTurno(); // Recargar el select de asignación
                
                // Recargar la grilla para reflejar cambios en las celdas
                cargarGrilla();
                
            } else {
                showToast(response.message || 'Error al guardar', 'error');
            }
        },
        error: function(xhr) {
            console.error('Error:', xhr);
            if (xhr.status === 422 && xhr.responseJSON && xhr.responseJSON.errors) {
                const errors = xhr.responseJSON.errors;
                $.each(errors, function(key, value) {
                    $('#' + key).addClass('is-invalid');
                    $('#' + key).siblings('.invalid-feedback').text(value[0]);
                });
                showToast('Por favor corrige los errores', 'error');
            } else if (xhr.responseJSON && xhr.responseJSON.message) {
                showToast(xhr.responseJSON.message, 'error');
            } else {
                showToast('Error al guardar el turno', 'error');
            }
        },
        complete: function() {
            submitBtn.text(originalText).prop('disabled', false);
        }
    });
}

// Función para actualizar todas las asignaciones que usan un turno específico
function actualizarAsignacionesPorTurno(turnoId, nuevosDatosTurno) {
    let asignacionesActualizadas = 0;
    
    // Recorrer todas las asignaciones locales
    Object.keys(asignacionesLocales).forEach(key => {
        const asignacion = asignacionesLocales[key];
        
        // Si esta asignación usa el turno que fue editado
        if (asignacion.id == turnoId) {
            // Actualizar los datos del turno
            asignacionesLocales[key] = {
                id: nuevosDatosTurno.id,
                nombre: nuevosDatosTurno.nombre,
                hora_entrada: nuevosDatosTurno.hora_entrada,
                hora_salida: nuevosDatosTurno.hora_salida,
                horas_trabajadas: nuevosDatosTurno.horas_trabajadas
            };
            asignacionesActualizadas++;
            
            // Extraer workerId y día de la key
            const [workerId, day] = key.split('_');
            // Actualizar la celda visualmente
            actualizarCeldaTurno(workerId, day);
        }
    });
    
    // Si se actualizaron asignaciones, recalcular todas las filas
    if (asignacionesActualizadas > 0) {
        // Obtener todos los workerIds únicos que fueron actualizados
        const workerIdsActualizados = new Set();
        Object.keys(asignacionesLocales).forEach(key => {
            const asignacion = asignacionesLocales[key];
            if (asignacion.id == turnoId) {
                const [workerId] = key.split('_');
                workerIdsActualizados.add(workerId);
            }
        });
        
        // Recalcular fila completa para cada trabajador afectado
        workerIdsActualizados.forEach(workerId => {
            actualizarFilaCompleta(workerId);
        });
        
        console.log(`✅ Actualizadas ${asignacionesActualizadas} asignaciones del turno ID ${turnoId}`);
        showToast(`Se actualizaron ${asignacionesActualizadas} asignaciones de turnos`, 'info');
    }
}

// Editar turno
function editarTurnoMantenedor(id) {
    $.ajax({
        url: BASE_URL + '/api/tipos-turno',
        method: 'GET',
        success: function(turnos) {
            const turno = turnos.find(t => t.id == id);
            if (turno) {
                $('#turno_id').val(turno.id);
                $('#nombre').val(turno.nombre);
                
                // Formatear horas sin segundos (HH:MM)
                let horaEntrada = turno.hora_entrada;
                let horaSalida = turno.hora_salida;
                
                // Si la hora tiene segundos (HH:MM:SS), recortar a HH:MM
                if (horaEntrada && horaEntrada.length > 5) {
                    horaEntrada = horaEntrada.substring(0, 5);
                }
                if (horaSalida && horaSalida.length > 5) {
                    horaSalida = horaSalida.substring(0, 5);
                }
                
                $('#hora_entrada').val(horaEntrada);
                $('#hora_salida').val(horaSalida);
                $('#formTitle').text('Editar Turno');
                $('#cancelEditBtn').show();
                calcularHorasMantenedor();
                
                // ELIMINADO: El scroll que movía la pantalla de fondo
                // $('html, body').animate({
                //     scrollTop: $('#turnoForm').offset().top - 100
                // }, 500);
            }
        },
        error: function() {
            showToast('Error al cargar los datos del turno', 'error');
        }
    });
}

// Eliminar turno
function eliminarTurnoMantenedor(id) {
    if (!confirm('¿Estás seguro de eliminar este turno? Esto también eliminará todas las asignaciones asociadas.')) return;
    
    // Primero, contar cuántas asignaciones serán afectadas
    let asignacionesAfectadas = 0;
    Object.keys(asignacionesLocales).forEach(key => {
        if (asignacionesLocales[key].id == id) {
            asignacionesAfectadas++;
        }
    });
    
    let mensajeConfirmacion = `¿Eliminar este turno?`;
    if (asignacionesAfectadas > 0) {
        mensajeConfirmacion = `Este turno está asignado a ${asignacionesAfectadas} celdas. ¿Eliminar de todas formas? Las asignaciones serán removidas.`;
    }
    
    if (!confirm(mensajeConfirmacion)) return;
    
    $.ajax({
        url: BASE_URL + '/api/tipos-turno/' + id,
        method: 'DELETE',
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        },
        success: function(response) {
            if (response.success) {
                // Eliminar todas las asignaciones locales que usan este turno
                let asignacionesEliminadas = 0;
                const workersAfectados = new Set();
                
                Object.keys(asignacionesLocales).forEach(key => {
                    if (asignacionesLocales[key].id == id) {
                        const [workerId, day] = key.split('_');
                        delete asignacionesLocales[key];
                        asignacionesEliminadas++;
                        workersAfectados.add(workerId);
                        // Limpiar la celda visualmente
                        actualizarCeldaTurno(workerId, day);
                    }
                });
                
                // Recalcular filas de trabajadores afectados
                workersAfectados.forEach(workerId => {
                    actualizarFilaCompleta(workerId);
                });
                
                showToast(response.message + (asignacionesEliminadas > 0 ? ` (${asignacionesEliminadas} asignaciones removidas)` : ''), 'success');
                cargarTurnosMantenedor(); // Recargar la tabla del mantenedor
                cargarTiposTurno(); // Recargar el select de asignación
                
                if ($('#turno_id').val() == id) {
                    resetFormMantenedor();
                }
            } else {
                showToast(response.message || 'Error al eliminar', 'error');
            }
        },
        error: function() {
            showToast('Error al eliminar el turno', 'error');
        }
    });
}

// ============================================
// FUNCIONES DE LA GRILLA PRINCIPAL
// ============================================

// Cargar tipos de turno
// Cargar tipos de turno
function cargarTiposTurno() {
    
    $.ajax({
        url: BASE_URL + '/api/tipos-turno',
        method: 'GET',
        success: function(data) {
            tipoTurnos = data;
            
            // Recargar el select
            recargarSelectTurnos();
            
        },
        error: function(xhr) {
            console.error('Error al cargar turnos:', xhr);
            showToast('Error al cargar los tipos de turno', 'error');
        }
    });
}


// Recargar el select con los turnos actuales
// Recargar el select con los turnos actuales
function recargarSelectTurnos() {
    
    const select = $('#turnoSelect');
    const valorActual = select.val();
    
    // Limpiar select
    select.empty();
    
    // Agregar opción por defecto
    select.append('<option value="">-- Sin turno --</option>');
    
    // Agregar cada turno como opción
    if (tipoTurnos && tipoTurnos.length > 0) {
        tipoTurnos.forEach(turno => {
            const horaEntrada = turno.hora_entrada ? turno.hora_entrada.substring(0, 5) : '';
            const horaSalida = turno.hora_salida ? turno.hora_salida.substring(0, 5) : '';
            const horas = turno.horas_trabajadas || 0;
            const optionText = `${turno.nombre} (${horaEntrada}-${horaSalida}) - ${horas} hrs`;
            select.append(`<option value="${turno.id}">${optionText}</option>`);
        });
    } else {
        console.warn('No hay turnos cargados en tipoTurnos');
        // Si no hay turnos, intentar cargarlos
        if (typeof cargarTiposTurno === 'function') {
            cargarTiposTurno();
        }
    }
    
    // Restaurar el valor anterior si existe
    if (valorActual) {
        select.val(valorActual);
    }
}

// Función auxiliar para cargar el turno en el select
// Versión ultra directa de cargarTurnoEnSelect
function cargarTurnoEnSelect(asignacionActual) {
    
    if (asignacionActual && asignacionActual.id) {
        // Método directo: buscar y seleccionar
        const selectElement = document.getElementById('turnoSelect');
        if (selectElement) {
            for (let i = 0; i < selectElement.options.length; i++) {
                if (selectElement.options[i].value == asignacionActual.id) {
                    selectElement.selectedIndex = i;
                    break;
                }
            }
        }
        
        // También intentar con jQuery
        $(`#turnoSelect option[value="${asignacionActual.id}"]`).prop('selected', true);
        
        // Forzar cambio visual
        $('#turnoSelect').trigger('change');
        
        // Verificar
        const finalValue = $('#turnoSelect').val();
        
        if (finalValue == asignacionActual.id) {
            $('#removeShiftBtn').prop('disabled', false);
            $('#removeShiftBtn').css('opacity', '1');
        } else {
            console.error('❌ Falló la selección. Valor esperado:', asignacionActual.id, 'Valor actual:', finalValue);
        }
    } else {
        $('#turnoSelect').val('');
        $('#removeShiftBtn').prop('disabled', true);
        $('#removeShiftBtn').css('opacity', '0.5');
    }
}


// Cargar grilla de turnos con paginación
function cargarGrilla() {
    $('#turnosGrid').html('<tr><td colspan="9" class="text-center">Cargando...</td></tr>');
    
    $.ajax({
        url: BASE_URL + '/api/turnos-grid',
        method: 'POST',
        data: {
            page: currentPage,
            per_page: perPage,
            search: searchTerm,
            _token: $('meta[name="csrf-token"]').attr('content')
        },
        success: function(response) {
            renderizarGrilla(response.data);
            renderizarPaginacion(response);
            // Actualizar contador con el total de registros (sin paginación)
            if (response.total) {
                $('#totalTrabajadoresGrilla').text(`Total: ${response.total} trabajadores`);
            }
        },
        error: function() {
            $('#turnosGrid').html('<tr><td colspan="10" class="text-center">Error al cargar...</td></tr>');
            $('#totalTrabajadoresGrilla').text('Total: -- trabajadores');
        }
    });
}


// Renderizar paginación
function renderizarPaginacion(data) {
    if (data.last_page <= 1) {
        $('#pagination').html('');
        return;
    }
    
    let html = '<nav><ul class="pagination pagination-sm justify-content-center">';
    
    if (data.current_page > 1) {
        html += `<li class="page-item"><a class="page-link" href="#" data-page="${data.current_page - 1}">Anterior</a></li>`;
    }
    
    for (let i = 1; i <= data.last_page; i++) {
        if (i === data.current_page) {
            html += `<li class="page-item active"><span class="page-link">${i}</span></li>`;
        } else if (Math.abs(i - data.current_page) <= 2) {
            html += `<li class="page-item"><a class="page-link" href="#" data-page="${i}">${i}</a></li>`;
        }
    }
    
    if (data.current_page < data.last_page) {
        html += `<li class="page-item"><a class="page-link" href="#" data-page="${data.current_page + 1}">Siguiente</a></li>`;
    }
    
    html += '</ul></nav>';
    $('#pagination').html(html);
}


// Guardar asignación de turno
$('#saveShiftBtn').on('click', function() {
    const turnoId = $('#turnoSelect').val();
    
    $.ajax({
        url: BASE_URL + '/api/asignar-turno',
        method: 'POST',
        data: {
            worker_id: currentAssign.workerId,
            day: currentAssign.day,
            tipo_turno_id: turnoId || null,
            _token: $('meta[name="csrf-token"]').attr('content')
        },
        success: function(response) {
            if (response.success) {
                $('#assignShiftModal').modal('hide');
                cargarGrilla();
                showToast(response.message, 'success');
            }
        },
        error: function() {
            showToast('Error al asignar turno', 'error');
        }
    });
});

// ============================================
// EVENTOS
// ============================================

// Eventos del mantenedor
$(document).ready(function() {

    $('#semanasRotacion').on('input change', function() {
        validarSemanasConLimite();
    });


    // Inicializar eventos del formulario del mantenedor
    $('#turnoForm').on('submit', guardarTurnoMantenedor);
    
    $('#cancelEditBtn').on('click', function() {
        resetFormMantenedor();
    });
    
    $('#hora_entrada, #hora_salida').on('change keyup', function() {
        calcularHorasMantenedor();
    });
    
    // Eventos para botones dinámicos del mantenedor (usando event delegation)
    $(document).on('click', '.edit-turno-btn', function() {
        const id = $(this).data('id');
        editarTurnoMantenedor(id);
    });
    
    $(document).on('click', '.delete-turno-btn', function() {
        const id = $(this).data('id');
        eliminarTurnoMantenedor(id);
    });
    
    // Cargar datos al abrir el modal del mantenedor
    $('#viewShiftsModal').on('show.bs.modal', function() {
        resetFormMantenedor();
        cargarTurnosMantenedor();
        cargarTiposTurno();
    });
    
    // Eventos de la grilla principal
    cargarTiposTurno();
    cargarGrilla();
});


$(document).on('click', '.assign-all-btn', function() {
    showToast('Funcionalidad próxima: Asignar todos los días', 'info');
});

$(document).on('click', '#pagination .page-link', function(e) {
    e.preventDefault();
    currentPage = $(this).data('page');
    cargarGrilla();
});

$('#perPage').on('change', function() {
    perPage = $(this).val();
    currentPage = 1;
    cargarGrilla();
});

let searchTimeout;
$('#searchWorker').on('input', function() {
    clearTimeout(searchTimeout);
    searchTimeout = setTimeout(() => {
        searchTerm = $(this).val();
        currentPage = 1;
        cargarGrilla();
    }, 500);
});

// ============================================
// FUNCIONES UTILITARIAS
// ============================================

function getDayName(day) {
    const days = { lunes: 'Lunes', martes: 'Martes', miercoles: 'Miércoles', jueves: 'Jueves', viernes: 'Viernes', sabado: 'Sábado', domingo: 'Domingo' };
    return days[day] || day;
}

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

function escapeHtml(text) {
    if (!text) return '';
    return text.replace(/[&<>]/g, function(m) {
        if (m === '&') return '&amp;';
        if (m === '<') return '&lt;';
        if (m === '>') return '&gt;';
        return m;
    });
}



// ============================================
// ASIGNACIÓN DE TURNOS (SOLO FRONTEND - SIN BD)
// ============================================

// Objeto para almacenar asignaciones temporales
// Estructura: { "workerId_day": { id: turnoId, hora_entrada: "09:00", hora_salida: "18:00", nombre: "Matutino" } }
let asignacionesLocales = {};

// Asignar turno a una celda (solo frontend)
function asignarTurnoLocal(workerId, day, turnoData) {
    const key = `${workerId}_${day}`;
    const horasTurno = turnoData ? parseFloat(turnoData.horas_trabajadas) : 0;
    
    // Obtener horas actuales del día (si ya tenía un turno asignado)
    const asignacionActual = asignacionesLocales[key];
    const horasActualesDia = asignacionActual ? parseFloat(asignacionActual.horas_trabajadas) : 0;
    
    // Calcular el cambio neto en horas (nuevas - actuales)
    const cambioHoras = horasTurno - horasActualesDia;
    
    // Calcular total actual sin el día
    let totalSinDia = 0;
    const days = ['lunes', 'martes', 'miercoles', 'jueves', 'viernes', 'sabado', 'domingo'];
    
    days.forEach(d => {
        if (d !== day) {
            const k = `${workerId}_${d}`;
            const asign = asignacionesLocales[k];
            if (asign && asign.horas_trabajadas) {
                totalSinDia += parseFloat(asign.horas_trabajadas);
            }
        }
    });
    
    // Si ya tenía turno, sumamos las horas actuales (porque totalSinDia no las incluye)
    // El total actual real es: totalSinDia + horasActualesDia
    const totalActual = totalSinDia + horasActualesDia;
    const totalPropuesto = totalActual + cambioHoras;
    
    
    if (turnoData && totalPropuesto > 42) {
        showToast(`⚠️ No se puede asignar. El trabajador excedería las 42 horas semanales (Total propuesto: ${totalPropuesto.toFixed(1)} hrs)`, 'error');
        return false;
    }
    
    if (!turnoData) {
        // Eliminar asignación
        delete asignacionesLocales[key];
    } else {
        // Guardar asignación
        asignacionesLocales[key] = {
            id: turnoData.id,
            nombre: turnoData.nombre,
            hora_entrada: turnoData.hora_entrada,
            hora_salida: turnoData.hora_salida,
            horas_trabajadas: turnoData.horas_trabajadas
        };
    }
    
    // Actualizar la celda en la grilla
    actualizarCeldaTurno(workerId, day);
    
    // Actualizar la fila completa (para recalcular total semanal)
    actualizarFilaCompleta(workerId);
    
    // Mostrar mensaje de éxito
    const mensaje = turnoData ? `Turno "${turnoData.nombre}" asignado (${turnoData.horas_trabajadas} hrs)` : 'Turno removido';
    showToast(mensaje, 'success');
    
    return true;
}

// Actualizar una celda específica en la grilla
function actualizarCeldaTurno(workerId, day) {
    const key = `${workerId}_${day}`;
    const asignacion = asignacionesLocales[key];
    
    let displayText = '—';
    let titleText = 'Sin turno';
    let bgColor = '#fff';
    
    if (asignacion) {
        // RECORTAR SEGUNDOS
        const horaEntrada = asignacion.hora_entrada.substring(0, 5);
        const horaSalida = asignacion.hora_salida.substring(0, 5);
        displayText = `${horaEntrada}-${horaSalida}`;
        titleText = `${asignacion.nombre} (${horaEntrada}-${horaSalida})`;
        bgColor = '#e8f5e9';
    }
    
    // Buscar y actualizar la celda
    const $celda = $(`.turno-cell[data-worker-id="${workerId}"][data-day="${day}"]`);
    if ($celda.length) {
        $celda.html(`<small title="${titleText}">${displayText}</small>`);
        $celda.css('background-color', bgColor);
    }
}

// Abrir modal para asignar turno
// Abrir modal para asignar turno
// Abrir modal para asignar turno
function abrirModalAsignarTurno(workerId, workerName, day, dayIndex) {
    
    // Guardar datos actuales
    currentAssign = { workerId, workerName, day, dayIndex };
    
    // Actualizar modal
    $('#modalWorkerName').text(workerName);
    $('#modalDay').text(getDayName(day));
    
    // Cargar turno actual si existe
    const key = `${workerId}_${day}`;
    const asignacionActual = asignacionesLocales[key];
    
    
    // Verificar que el select tiene opciones
    if ($('#turnoSelect option').length <= 1) {
        recargarSelectTurnos();
        // Esperar a que se recargue el select
        setTimeout(() => {
            cargarTurnoEnSelect(asignacionActual);
        }, 200);
    } else {
        cargarTurnoEnSelect(asignacionActual);
    }
    
    // Abrir modal
    $('#assignShiftModal').modal('show');
}


// Función auxiliar para cargar el turno en el select
function cargarTurnoEnSelect(asignacionActual) {
    // Limpiar el select primero
    $('#turnoSelect').val('');
    
    if (asignacionActual) {
        // Hay turno asignado - seleccionar el turno en el select

        $('#turnoSelect').val(asignacionActual.id);
        
        // Verificar que se seleccionó correctamente
        const valorSeleccionado = $('#turnoSelect').val();

        
        if (valorSeleccionado != asignacionActual.id) {
            console.warn('No se pudo seleccionar el turno. Buscando opción...');
            // Buscar la opción y seleccionarla manualmente
            $(`#turnoSelect option[value="${asignacionActual.id}"]`).prop('selected', true);
        }
        
        // Habilitar el botón "Quitar Turno"
        $('#removeShiftBtn').prop('disabled', false);
        $('#removeShiftBtn').css('opacity', '1');
    } else {
        // No hay turno asignado - select vacío
   
        $('#turnoSelect').val('');
        
        // Deshabilitar el botón "Quitar Turno"
        $('#removeShiftBtn').prop('disabled', true);
        $('#removeShiftBtn').css('opacity', '0.5');
        $('#removeShiftBtn').css('cursor', 'not-allowed');
    }
}


// Guardar asignación de turno (SOLO FRONTEND)
function guardarAsignacionTurno() {
    const turnoId = $('#turnoSelect').val();
    const { workerId, day } = currentAssign;
    
    if (!turnoId || turnoId === '') {
        // No se seleccionó ningún turno
        showToast('Por favor selecciona un turno', 'info');
        return;
    }
    
    // Buscar el turno seleccionado en tipoTurnos
    const turnoSeleccionado = tipoTurnos.find(t => t.id == turnoId);
    if (turnoSeleccionado) {
        const horasTrabajadas = parseFloat(turnoSeleccionado.horas_trabajadas) || 0;
        
        const resultado = asignarTurnoLocal(workerId, day, {
            id: turnoSeleccionado.id,
            nombre: turnoSeleccionado.nombre,
            hora_entrada: turnoSeleccionado.hora_entrada,
            hora_salida: turnoSeleccionado.hora_salida,
            horas_trabajadas: horasTrabajadas
        });
        
        if (resultado) {
            // Actualizar el estado del botón después de asignar
            const key = `${workerId}_${day}`;
            const nuevaAsignacion = asignacionesLocales[key];
            if (nuevaAsignacion) {
                $('#removeShiftBtn').prop('disabled', false);
                $('#removeShiftBtn').css('opacity', '1');
            }
            $('#assignShiftModal').modal('hide');
        }
    }
}


// Quitar turno (versión alternativa más robusta)
// Quitar turno (versión simplificada que funciona directamente)
function quitarTurno() {

    const { workerId, day } = currentAssign;
    
    if (!workerId || !day) {
        showToast('Error: No hay selección activa', 'error');
        return;
    }
    
    const key = `${workerId}_${day}`;
    const asignacionActual = asignacionesLocales[key];
    
    if (!asignacionActual) {
        showToast('No hay turno asignado para quitar', 'info');
        return;
    }
    
    // Confirmar acción
    if (confirm(`¿Quitar el turno "${asignacionActual.nombre}"?`)) {
        // 1. Eliminar directamente de asignacionesLocales
        delete asignacionesLocales[key];

        
        // 2. Actualizar la celda visualmente a vacío
        const $celda = $(`.turno-cell[data-worker-id="${workerId}"][data-day="${day}"]`);
        if ($celda.length) {
            $celda.html('<small title="Sin turno">—</small>');
            $celda.css('background-color', '#fff');
            $celda.removeClass('border border-danger');
        }
        
        // 3. Actualizar la fila completa (recalcular total semanal)
        actualizarFilaCompleta(workerId);
        
        // 4. Limpiar el select del modal
        $('#turnoSelect').val('');
        
        // 5. Deshabilitar el botón quitar turno
        $('#removeShiftBtn').prop('disabled', true);
        $('#removeShiftBtn').css('opacity', '0.5');
        
        // 6. Mostrar mensaje de éxito
        showToast('Turno removido correctamente', 'success');
        
        // 7. Cerrar modal
        $('#assignShiftModal').modal('hide');
    }
}

// Limpiar todos los turnos de un trabajador
function limpiarTurnosTrabajador(workerId, workerName) {
    if (confirm(`¿Estás seguro de limpiar TODOS los turnos de ${workerName}?`)) {
        const days = ['lunes', 'martes', 'miercoles', 'jueves', 'viernes', 'sabado', 'domingo'];
        let limpiados = 0;
        
        days.forEach(day => {
            const key = `${workerId}_${day}`;
            if (asignacionesLocales[key]) {
                delete asignacionesLocales[key];
                limpiados++;
                actualizarCeldaTurno(workerId, day);
            }
        });
        
        if (limpiados > 0) {
            actualizarFilaCompleta(workerId);
            showToast(`Se limpiaron ${limpiados} turnos de ${workerName}`, 'success');
        } else {
            showToast(`${workerName} no tiene turnos asignados`, 'info');
        }
    }
}

// Exportar asignaciones (para depuración)
function exportarAsignaciones() {

    const total = Object.keys(asignacionesLocales).length;
    showToast(`Total de turnos asignados: ${total}`, 'info');
}

// ============================================
// RENDERIZADO DE GRILLA CON ASIGNACIONES LOCALES
// ============================================


// Renderizar grilla con columna de total de horas
function renderizarGrilla(workers) {
    if (!workers || workers.length === 0) {
        $('#turnosGrid').html('<tr><td colspan="10" class="text-center">No hay trabajadores...</td></tr>');
        $('#totalTrabajadoresGrilla').text('Total: 0 trabajadores');
        return;
    }
    
    // Actualizar contador con el total de la respuesta (si viene de la API)
    // Asumiendo que en la respuesta también viene el total general


    if (!workers || workers.length === 0) {
        $('#turnosGrid').html('<tr><td colspan="10" class="text-center">No hay trabajadores...</td></tr>');
        return;
    }
    
    const days = ['lunes', 'martes', 'miercoles', 'jueves', 'viernes', 'sabado', 'domingo'];
    
    let html = '';
    workers.forEach(worker => {
        // Calcular total de horas semanales para este trabajador
        const totalHoras = calcularTotalHorasSemanales(worker.id);
        const excedeLimite = totalHoras > 42;
        
        // Determinar clase CSS y símbolo para el total
        let totalClass = 'fw-bold text-center';
        let warningSymbol = '';
        if (excedeLimite) {
            totalClass += ' text-danger';
            warningSymbol = ' ⚠️';
        }
        
        html += `<tr>
            <td class="fw-bold">
                ${escapeHtml(worker.nombre)}<br>
                <small class="text-muted">${worker.rut}</small>
                <button class="btn btn-sm btn-outline-danger clear-worker-turnos mt-1" 
                        data-worker-id="${worker.id}"
                        data-worker-name="${escapeHtml(worker.nombre)}"
                        style="font-size: 0.7rem; display: block; width: 100%;">
                    🗑️ Limpiar todo
                </button>
            </td>`;
        
        // Días de la semana
        days.forEach((day, index) => {
            const key = `${worker.id}_${day}`;
            const asignacion = asignacionesLocales[key];
            
            let displayText = '—';
            let titleText = 'Sin turno';
            let bgColor = '#fff';
            let horasCelda = 0;
            
            if (asignacion) {
                const horaEntrada = asignacion.hora_entrada.substring(0, 5);
                const horaSalida = asignacion.hora_salida.substring(0, 5);
                displayText = `${horaEntrada}-${horaSalida}`;
                titleText = `${asignacion.nombre} (${horaEntrada}-${horaSalida}) - ${asignacion.horas_trabajadas} hrs`;
                bgColor = '#e8f5e9';
                horasCelda = asignacion.horas_trabajadas;
            }
            
            // Si la celda excede el límite semanal, mostrar advertencia visual
            let cellWarning = '';
            if (excedeLimite && asignacion) {
                cellWarning = ' border border-danger';
            }
            
            html += `<td class="text-center turno-cell${cellWarning}" 
                           style="cursor: pointer; background-color: ${bgColor}"
                           data-worker-id="${worker.id}"
                           data-worker-name="${escapeHtml(worker.nombre)}"
                           data-day="${day}"
                           data-day-index="${index}">
                        <small title="${titleText}">${displayText}</small>
                       </td>`;
        });
        
        // Columna de total de horas semanales
        html += `<td class="${totalClass}" style="background-color: ${excedeLimite ? '#ffebee' : '#f5f5f5'}">
                    ${totalHoras.toFixed(1)} hrs${warningSymbol}
                    ${excedeLimite ? '<br><small class="text-danger">⚠️ Excede 42 hrs</small>' : ''}
                </td>
             </tr>`;
    });
    
    $('#turnosGrid').html(html);
}
// ============================================
// EVENTOS DE ASIGNACIÓN DE TURNOS
// ============================================

// Evento para abrir modal al hacer clic en una celda
$(document).on('click', '.turno-cell', function() {
    const workerId = $(this).data('worker-id');
    const workerName = $(this).data('worker-name');
    const day = $(this).data('day');
    const dayIndex = $(this).data('day-index');
    abrirModalAsignarTurno(workerId, workerName, day, dayIndex);
});

// Evento para guardar asignación
$('#saveShiftBtn').off('click').on('click', function() {
    guardarAsignacionTurno();
});

// Evento para limpiar todos los turnos de un trabajador
$(document).on('click', '.clear-worker-turnos', function(e) {
    e.stopPropagation();
    const workerId = $(this).data('worker-id');
    const workerName = $(this).data('worker-name');
    limpiarTurnosTrabajador(workerId, workerName);
});

// Evento para asignar todos los días (funcionalidad futura)
$(document).on('click', '.assign-all-btn', function() {
    const workerId = $(this).data('worker-id');
    const workerName = $(this).data('worker-name');
    showToast(`Funcionalidad próxima: Asignar turnos para toda la semana a ${workerName}`, 'info');
});

// Evento para cerrar modal con ESC
$('#assignShiftModal').off('hidden.bs.modal').on('hidden.bs.modal', function() {
    // No hacer nada al cerrar
});

// Presionar Enter en el select para guardar
$('#turnoSelect').off('keypress').on('keypress', function(e) {
    if (e.which === 13) {
        e.preventDefault();
        guardarAsignacionTurno();
    }
});

// Calcular total de horas semanales para un trabajador
function calcularTotalHorasSemanales(workerId) {
    const days = ['lunes', 'martes', 'miercoles', 'jueves', 'viernes', 'sabado', 'domingo'];
    let totalHoras = 0;
    
    days.forEach(day => {
        const key = `${workerId}_${day}`;
        const asignacion = asignacionesLocales[key];
        if (asignacion && asignacion.horas_trabajadas) {
            totalHoras += parseFloat(asignacion.horas_trabajadas);
        }
    });
    
    return totalHoras;
}

// Verificar si un trabajador excede el límite al asignar un turno
function verificarLimiteSemanal(workerId, nuevasHoras, diaActual) {
    // Calcular horas actuales EXCLUYENDO el día que vamos a modificar
    let horasSinDiaActual = 0;
    const days = ['lunes', 'martes', 'miercoles', 'jueves', 'viernes', 'sabado', 'domingo'];
    
    days.forEach(day => {
        if (day !== diaActual) {
            const key = `${workerId}_${day}`;
            const asignacion = asignacionesLocales[key];
            if (asignacion && asignacion.horas_trabajadas) {
                horasSinDiaActual += parseFloat(asignacion.horas_trabajadas);
            }
        }
    });
    
    // El total propuesto es: horas de otros días + nuevas horas del día actual
    const totalPropuesto = horasSinDiaActual + nuevasHoras;
        
    return totalPropuesto <= 42;
}


// Actualizar toda la fila de un trabajador (para recalcular total semanal)
function actualizarFilaCompleta(workerId) {
    // Obtener los datos del trabajador desde la grilla actual
    const $fila = $(`#turnosGrid tr:has(td .clear-worker-turnos[data-worker-id="${workerId}"])`);
    if ($fila.length) {
        // Recalcular total
        const totalHoras = calcularTotalHorasSemanales(workerId);
        const excedeLimite = totalHoras > 42;
        
        // Actualizar la celda de total
        const $totalCelda = $fila.find('td:last-child');
        let warningSymbol = '';
        if (excedeLimite) {
            warningSymbol = ' ⚠️';
        }
        
        $totalCelda.html(`
            <div class="fw-bold text-center ${excedeLimite ? 'text-danger' : ''}" style="background-color: ${excedeLimite ? '#ffebee' : '#f5f5f5'}">
                ${totalHoras.toFixed(1)} hrs${warningSymbol}
                ${excedeLimite ? '<br><small class="text-danger">⚠️ Excede 42 hrs</small>' : ''}
            </div>
        `);
        $totalCelda.css('background-color', excedeLimite ? '#ffebee' : '#f5f5f5');
        
        // Actualizar bordes de las celdas que exceden
        const days = ['lunes', 'martes', 'miercoles', 'jueves', 'viernes', 'sabado', 'domingo'];
        days.forEach((day, index) => {
            const key = `${workerId}_${day}`;
            const asignacion = asignacionesLocales[key];
            const $celda = $fila.find(`td:eq(${index + 1})`);
            
            if (excedeLimite && asignacion) {
                $celda.addClass('border border-danger');
                $celda.css('background-color', '#ffebee');
            } else if (asignacion) {
                $celda.removeClass('border border-danger');
                $celda.css('background-color', '#e8f5e9');
            } else {
                $celda.removeClass('border border-danger');
                $celda.css('background-color', '#fff');
            }
        });
    }
}

// Evento para quitar turno - COLOCAR ESTO ANTES DEL ÚLTIMO
$('#removeShiftBtn').off('click').on('click', function(e) {
    e.preventDefault();
    quitarTurno();
});


// Formatear hora mientras escribe
function formatearHora(input) {
    let valor = input.value.replace(/[^0-9]/g, '');
    if (valor.length >= 2) {
        valor = valor.slice(0,2) + ':' + valor.slice(2,4);
    }
    input.value = valor.slice(0,5);
}

// Aplicar a los inputs
$('#hora_entrada, #hora_salida').on('input', function() {
    formatearHora(this);
});



// Actualizar contador de trabajadores en la grilla
function actualizarContadorGrilla() {
    // Obtener el total de la paginación actual o del total general
    $.ajax({
        url: BASE_URL + '/api/total-trabajadores',
        method: 'GET',
        success: function(response) {
            $('#totalTrabajadoresGrilla').text(`Total: ${response.total} trabajadores`);
        },
        error: function() {
            $('#totalTrabajadoresGrilla').text('Total: -- trabajadores');
        }
    });
}


function validarSemanasConLimite() {
    
    const maxSemanas = 20;
    let valor = parseInt($('#semanasRotacion').val());
            
    if (isNaN(valor)) valor = 1;
    if (valor < 1) valor = 1;
    if (valor > maxSemanas) {
        valor = maxSemanas;
        $('#semanasRotacion').val(maxSemanas);
        showToast(`El máximo de semanas es ${maxSemanas} !`, 'info');
    }
            
    $('#semanasRotacion').val(valor);
}


</script>
@endpush
  
@endsection