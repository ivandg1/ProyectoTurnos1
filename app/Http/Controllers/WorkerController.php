<?php

namespace App\Http\Controllers;

use App\Models\Worker;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class WorkerController extends Controller
{
    public function index()
    {
        $workers = Worker::all();
        return view('workers.index', compact('workers'));
    }

    public function store(Request $request): JsonResponse
    {
        try {
            // Validación manual
            $validated = $request->validate([
                'rut' => 'required|string|max:12|unique:workers,rut',
                'nombre' => 'required|string|max:100'
            ], [
                'rut.required' => 'El RUT es obligatorio',
                'rut.unique' => 'Este RUT ya está registrado',
                'nombre.required' => 'El nombre es obligatorio'
            ]);

            $worker = Worker::create($validated);
            
            return response()->json([
                'success' => true,
                'message' => 'Trabajador agregado correctamente',
                'worker' => [
                    'id' => $worker->id,
                    'rut' => $worker->rut,
                    'nombre' => $worker->nombre,
                    'created_at' => $worker->created_at->format('d/m/Y H:i')
                ]
            ]);
            
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error: ' . $e->getMessage()
            ], 500);
        }
    }

    public function destroy(Worker $worker): JsonResponse
    {
        try {
            $worker->delete();
            
            return response()->json([
                'success' => true,
                'message' => 'Trabajador eliminado correctamente'
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al eliminar'
            ], 500);
        }
    }
}