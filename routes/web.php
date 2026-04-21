<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\WorkerController;
use Illuminate\Http\Request;
use App\Models\Worker;

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