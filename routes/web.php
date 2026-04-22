<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\WorkerController;
use App\Http\Controllers\TipoTurnoController;
use App\Http\Controllers\TurnoController;
use App\Models\TurnoTemp;  // ← Agregar esta línea
use Illuminate\Http\Request;
use App\Models\Worker;
use App\Models\TurnoRotacionTemp;

Route::get('/', [WorkerController::class, 'index']);

// Ruta alternativa sin nombre específico
Route::post('/guardar-trabajador', function(Request $request) {
    try {
        $worker = Worker::create([
            'rut' => $request->rut,
            'nombre' => $request->nombre
        ]);
        
        return response()->json([
            'success' => true,
            'worker' => [
                'id' => $worker->id,
                'rut' => $worker->rut,
                'nombre' => $worker->nombre,
                'created_at' => $worker->created_at->format('d/m/Y H:i')
            ]
        ]);
    } catch(\Exception $e) {
        return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
    }
});

Route::delete('/eliminar-trabajador/{id}', function($id) {
    $worker = Worker::find($id);
    if($worker) {
        $worker->delete();
        return response()->json(['success' => true]);
    }
    return response()->json(['success' => false], 404);
});

// Ruta para la grilla de turnos
Route::get('/turnos', [App\Http\Controllers\TurnoController::class, 'index'])->name('turnos.index');

Route::prefix('api/tipos-turno')->group(function () {
    Route::get('/', [App\Http\Controllers\TipoTurnoController::class, 'index']);
    Route::post('/', [App\Http\Controllers\TipoTurnoController::class, 'store']);
    Route::put('/{id}', [App\Http\Controllers\TipoTurnoController::class, 'update']);
    Route::delete('/{id}', [App\Http\Controllers\TipoTurnoController::class, 'destroy']);
});


Route::post('/api/turnos-grid', function(Request $request) {
    $search = $request->search;
    $perPage = $request->per_page ?? 25;
    
    $query = Worker::query();
    
    if ($search) {
        $query->where('nombre', 'LIKE', "%{$search}%")
              ->orWhere('rut', 'LIKE', "%{$search}%");
    }
    
    $total = $query->count(); // Total sin paginación
    $workers = $query->paginate($perPage);
    
    return response()->json([
        'data' => $workers->items(),
        'total' => $total,
        'current_page' => $workers->currentPage(),
        'last_page' => $workers->lastPage(),
        'per_page' => $workers->perPage()
    ]);
});

Route::post('/api/get-turno-asignado', function(Request $request) {
    // Pendiente: obtener turno asignado
    return response()->json(['turno_id' => null]);
});

Route::post('/api/asignar-turno', function(Request $request) {
    // Pendiente: guardar asignación
    return response()->json(['success' => true, 'message' => 'Turno asignado']);
});

// Ruta para obtener el total de trabajadores
Route::get('/api/total-trabajadores', function() {
    $total = App\Models\Worker::count();
    return response()->json(['total' => $total]);
});

// Ruta para la vista previa de rotación de turnos
Route::get('/preview-rotacion', [App\Http\Controllers\TurnoController::class, 'preview'])->name('rotacion.preview');


// Rutas para turnos temporales
Route::post('/api/turnos-temp/guardar', function(Request $request) {
    try {
        $asignaciones = $request->input('asignaciones', []);
        
        // Limpiar turnos temporales existentes (opcional, si quieres sobrescribir)
        // App\Models\TurnoTemp::truncate();
        
        $guardados = 0;
        foreach ($asignaciones as $key => $asignacion) {
            // La key tiene formato "workerId_dia"
            $parts = explode('_', $key);
            if (count($parts) >= 2) {
                $workerId = $parts[0];
                $dia = $parts[1];

                // Truncar segundos de las horas (solo HH:MM)
                $horaEntrada = isset($asignacion['hora_entrada']) ? substr($asignacion['hora_entrada'], 0, 5) : null;
                $horaSalida = isset($asignacion['hora_salida']) ? substr($asignacion['hora_salida'], 0, 5) : null;
                
                TurnoTemp::updateOrCreate(
                    [
                        'worker_id' => $workerId,
                        'dia' => $dia
                    ],
                    [
                        'tipo_turno_id' => $asignacion['id'] ?? null,
                        'hora_entrada' => $horaEntrada ?? null,
                        'hora_salida' => $horaSalida ?? null,
                        'nombre_turno' => $asignacion['nombre'] ?? null,
                        'horas_trabajadas' => $asignacion['horas_trabajadas'] ?? 0
                    ]
                );
                $guardados++;
            }
        }
        
        return response()->json([
            'success' => true,
            'message' => "Se guardaron {$guardados} asignaciones correctamente",
            'total' => $guardados
        ]);
    } catch (\Exception $e) {
        return response()->json([
            'success' => false,
            'message' => 'Error al guardar: ' . $e->getMessage()
        ], 500);
    }
});

Route::get('/api/turnos-temp/cargar', function() {
    try {
        $turnos = App\Models\TurnoTemp::all();
        $asignaciones = [];
        
        foreach ($turnos as $turno) {
            $key = $turno->worker_id . '_' . $turno->dia;
            $asignaciones[$key] = [
                'id' => $turno->tipo_turno_id,
                'nombre' => $turno->nombre_turno,
                'hora_entrada' => $turno->hora_entrada,
                'hora_salida' => $turno->hora_salida,
                'horas_trabajadas' => $turno->horas_trabajadas
            ];
        }
        
        return response()->json([
            'success' => true,
            'asignaciones' => $asignaciones
        ]);
    } catch (\Exception $e) {
        return response()->json([
            'success' => false,
            'message' => 'Error al cargar: ' . $e->getMessage()
        ], 500);
    }
});

Route::delete('/api/turnos-temp/limpiar', function() {
    try {
        App\Models\TurnoTemp::truncate();
        return response()->json([
            'success' => true,
            'message' => 'Todas las asignaciones temporales fueron eliminadas'
        ]);
    } catch (\Exception $e) {
        return response()->json([
            'success' => false,
            'message' => 'Error al limpiar: ' . $e->getMessage()
        ], 500);
    }
});


// Rutas para turnos de rotación temporal
Route::post('/api/turnos-rotacion/guardar', function(Request $request) {
    try {
        $turnosRotacion = $request->input('turnos_rotacion', []);
        $parametros = $request->input('parametros', []);
        
        // Opcional: Limpiar rotaciones anteriores
        if ($request->input('limpiar_anteriores', true)) {
            TurnoRotacionTemp::truncate();
        }
        
        $guardados = 0;
        foreach ($turnosRotacion as $turno) {
            TurnoRotacionTemp::create([
                'worker_id' => $turno['worker_id'],
                'fecha_asignada' => $turno['fecha_asignada'],
                'dia' => $turno['dia'],
                'tipo_turno_id' => $turno['tipo_turno_id'] ?? null,
                'hora_entrada' => $turno['hora_entrada'] ?? null,
                'hora_salida' => $turno['hora_salida'] ?? null,
                'nombre_turno' => $turno['nombre_turno'] ?? null,
                'horas_trabajadas' => $turno['horas_trabajadas'] ?? 0,
                'semana' => $turno['semana'] ?? null,
                'parametros_rotacion' => $parametros
            ]);
            $guardados++;
        }
        
        return response()->json([
            'success' => true,
            'message' => "Se guardaron {$guardados} turnos de rotación correctamente",
            'total' => $guardados
        ]);
    } catch (\Exception $e) {
        return response()->json([
            'success' => false,
            'message' => 'Error al guardar rotación: ' . $e->getMessage()
        ], 500);
    }
});

Route::get('/api/turnos-rotacion/cargar', function(Request $request) {
    try {
        $query = TurnoRotacionTemp::with('worker');
        
        // Filtrar por semana si se especifica
        if ($request->has('semana')) {
            $query->where('semana', $request->semana);
        }
        
        // Filtrar por trabajador si se especifica
        if ($request->has('worker_id')) {
            $query->where('worker_id', $request->worker_id);
        }
        
        $turnos = $query->orderBy('fecha_asignada')->orderBy('worker_id')->get();
        
        // Agrupar por semana para facilitar la visualización
        $turnosPorSemana = [];
        $parametrosUsados = null;
        
        foreach ($turnos as $turno) {
            $semana = $turno->semana;
            if (!isset($turnosPorSemana[$semana])) {
                $turnosPorSemana[$semana] = [];
            }
            $turnosPorSemana[$semana][] = $turno;
            
            if (!$parametrosUsados && $turno->parametros_rotacion) {
                $parametrosUsados = $turno->parametros_rotacion;
            }
        }
        
        return response()->json([
            'success' => true,
            'turnos_por_semana' => $turnosPorSemana,
            'parametros' => $parametrosUsados,
            'total_semanas' => count($turnosPorSemana),
            'total_turnos' => $turnos->count()
        ]);
    } catch (\Exception $e) {
        return response()->json([
            'success' => false,
            'message' => 'Error al cargar rotación: ' . $e->getMessage()
        ], 500);
    }
});

Route::delete('/api/turnos-rotacion/limpiar', function() {
    try {
        TurnoRotacionTemp::truncate();
        return response()->json([
            'success' => true,
            'message' => 'Todos los turnos de rotación fueron eliminados'
        ]);
    } catch (\Exception $e) {
        return response()->json([
            'success' => false,
            'message' => 'Error al limpiar: ' . $e->getMessage()
        ], 500);
    }
});


// Ruta para obtener datos de rotación
Route::get('/api/rotacion-data', [App\Http\Controllers\TurnoController::class, 'getRotacionData']);