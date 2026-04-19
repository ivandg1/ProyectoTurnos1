@extends('layouts.app')

@section('content')
<meta name="csrf-token" content="{{ csrf_token() }}">
<div class="row">
    <div class="col-md-12">
        <div class="card card-custom">
            <div class="card-header bg-white d-flex justify-content-between align-items-center">
                <h4 class="mb-0">Lista de Trabajadores</h4>
                <div>
                    <a href="{{ url('/turnos') }}" class="btn btn-info me-2">
                        Siguiente → Grilla de Turnos
                    </a>
                    <button type="button" class="btn btn-custom" data-bs-toggle="modal" data-bs-target="#workerModal">
                        + Agregar Trabajador
                    </button>
                </div>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-hover table-striped">
                        <thead class="table-dark">
                            <tr>
                                <th>ID</th>
                                <th>RUT</th>
                                <th>Nombre Completo</th>
                                <th>Fecha de Registro</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody id="workers-table">
                            @forelse($workers as $worker)
                            <tr id="worker-{{ $worker->id }}">
                                <td>{{ $worker->id }}</td>
                                <td>{{ $worker->rut }}</td>
                                <td>{{ $worker->nombre }}</td>
                                <td>{{ $worker->created_at->format('d/m/Y H:i') }}</td>
                                <td>
                                    <button class="btn btn-danger btn-sm delete-worker" data-id="{{ $worker->id }}">
                                        Eliminar
                                    </button>
                                </td>
                            </tr>
                            @empty
                            <tr id="no-data-row">
                                <td colspan="5" class="text-center">No hay trabajadores registrados</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal para agregar trabajador -->
<div class="modal fade" id="workerModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Agregar Nuevo Trabajador</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="workerForm">
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="rut" class="form-label">RUT</label>
                        <input type="text" class="form-control" id="rut" name="rut" required 
                               placeholder="Ej: 12345678-9">
                        <div class="invalid-feedback"></div>
                    </div>
                    <div class="mb-3">
                        <label for="nombre" class="form-label">Nombre Completo</label>
                        <input type="text" class="form-control" id="nombre" name="nombre" required
                               placeholder="Ej: Juan Pérez González">
                        <div class="invalid-feedback"></div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-custom" id="submitBtn">Guardar Trabajador</button>
                </div>
            </form>
        </div>
    </div>
</div>

@push('scripts')
<script>
$(document).ready(function() {
    let isSubmitting = false;
    const BASE_URL = '/ProyectoTurnos1/public';
    
    // Agregar trabajador vía AJAX
    $('#workerForm').on('submit', function(e) {
        e.preventDefault();
        
        if (isSubmitting) {
            return;
        }
        
        // Limpiar errores
        $('.is-invalid').removeClass('is-invalid');
        $('.invalid-feedback').text('');
        
        // Validar campos vacíos
        const rut = $('#rut').val().trim();
        const nombre = $('#nombre').val().trim();
        
        if (!rut) {
            $('#rut').addClass('is-invalid').siblings('.invalid-feedback').text('El RUT es obligatorio');
            return;
        }
        
        if (!nombre) {
            $('#nombre').addClass('is-invalid').siblings('.invalid-feedback').text('El nombre es obligatorio');
            return;
        }
        
        // Preparar datos
        const formData = {
            rut: rut,
            nombre: nombre,
            _token: $('meta[name="csrf-token"]').attr('content')
        };
        
        isSubmitting = true;
        const $submitBtn = $('#submitBtn');
        const originalText = $submitBtn.text();
        $submitBtn.text('Guardando...').prop('disabled', true);
        
        // Enviar petición
        $.ajax({
            url: BASE_URL +'/guardar-trabajador',
            method: 'POST',
            data: formData,
            dataType: 'json',
            headers: {
                'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'),
                'X-Requested-With': 'XMLHttpRequest'
            },
            success: function(response) {
                if (response.success) {
                    addWorkerToTable(response.worker);
                    $('#workerForm')[0].reset();
                    $('#workerModal').modal('hide');
                    showToast('✓ Trabajador agregado correctamente', 'success');
                } else {
                    showToast(response.message || 'Error al guardar', 'error');
                }
            },
            error: function(xhr) {
                console.log('Error en la petición:', xhr);
                
                if (xhr.status === 422) {
                    // Errores de validación
                    const errors = xhr.responseJSON.errors;
                    $.each(errors, function(key, value) {
                        $('#' + key).addClass('is-invalid');
                        $('#' + key).siblings('.invalid-feedback').text(value[0]);
                    });
                    showToast('Por favor corrige los errores', 'error');
                } else if (xhr.status === 419) {
                    showToast('La sesión expiró. Recargando página...', 'error');
                    setTimeout(() => location.reload(), 2000);
                } else if (xhr.status === 500) {
                    showToast('Error del servidor. Revisa los logs.', 'error');
                } else {
                    let errorMsg = 'Error al guardar el trabajador';
                    if (xhr.responseJSON && xhr.responseJSON.message) {
                        errorMsg = xhr.responseJSON.message;
                    } else if (xhr.responseText) {
                        try {
                            const data = JSON.parse(xhr.responseText);
                            errorMsg = data.message || data.error || errorMsg;
                        } catch(e) {}
                    }
                    showToast(errorMsg, 'error');
                }
            },
            complete: function() {
                isSubmitting = false;
                $submitBtn.text(originalText).prop('disabled', false);
            }
        });
    });
    
    function addWorkerToTable(worker) {
        if (!$('#no-data-row').length) {
            $('#workers-table').prepend(createWorkerRow(worker));
        } else {
            $('#no-data-row').remove();
            $('#workers-table').html(createWorkerRow(worker));
        }
        attachDeleteEvent();
    }
    
    function createWorkerRow(worker) {
        return `
            <tr id="worker-${worker.id}">
                <td>${worker.id}</td>
                <td>${escapeHtml(worker.rut)}</td>
                <td>${escapeHtml(worker.nombre)}</td>
                <td>${worker.created_at}</td>
                <td>
                    <button class="btn btn-danger btn-sm delete-worker" data-id="${worker.id}">
                        Eliminar
                    </button>
                 </td>
             </tr>
        `;
    }
    
    function escapeHtml(text) {
        if (!text) return '';
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }
    
    function attachDeleteEvent() {
        $('.delete-worker').off('click').on('click', function() {
            if (!confirm('¿Eliminar este trabajador?')) return;
            
            const workerId = $(this).data('id');
            const $row = $('#worker-' + workerId);
            
            $.ajax({
                url: BASE_URL +'/eliminar-trabajador/' + workerId, 
                method: 'DELETE',
                headers: {
                    'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content'),
                    'X-Requested-With': 'XMLHttpRequest'
                },
                success: function(response) {
                    if (response.success) {
                        $row.fadeOut(300, function() {
                            $(this).remove();
                            if ($('#workers-table tr').length === 0) {
                                $('#workers-table').html('<tr id="no-data-row"><td colspan="5" class="text-center">No hay trabajadores registrados</td></tr>');
                            }
                        });
                        showToast('✓ Trabajador eliminado', 'success');
                    }
                },
                error: function(xhr) {
                    showToast('Error al eliminar', 'error');
                }
            });
        });
    }
    
    function showToast(message, type = 'success') {
        if ($('#toast-container').length === 0) {
            $('body').append('<div id="toast-container" style="position: fixed; top: 20px; right: 20px; z-index: 9999;"></div>');
        }
        
        const bgColor = type === 'success' ? 'bg-success' : 'bg-danger';
        const icon = type === 'success' ? '✓' : '✗';
        
        const toast = $(`
            <div class="toast align-items-center text-white ${bgColor} border-0 mb-2" role="alert" data-bs-autohide="true" data-bs-delay="3000">
                <div class="d-flex">
                    <div class="toast-body">
                        ${icon} ${message}
                    </div>
                    <button type="button" class="btn-close btn-close-white me-2 m-auto" data-bs-dismiss="toast"></button>
                </div>
            </div>
        `);
        
        $('#toast-container').append(toast);
        const bsToast = new bootstrap.Toast(toast);
        bsToast.show();
        
        toast.on('hidden.bs.toast', function() {
            $(this).remove();
        });
    }
    
    $('#rut, #nombre').on('input', function() {
        $(this).removeClass('is-invalid');
        $(this).siblings('.invalid-feedback').text('');
    });
    
    $('#workerModal').on('hidden.bs.modal', function() {
        $('#workerForm')[0].reset();
        $('.is-invalid').removeClass('is-invalid');
        isSubmitting = false;
        $('#submitBtn').text('Guardar Trabajador').prop('disabled', false);
    });
    
    attachDeleteEvent();
});
</script>
@endpush
@endsection