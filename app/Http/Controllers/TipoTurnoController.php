<?php

namespace App\Http\Controllers;

use App\Models\TipoTurno;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class TipoTurnoController extends Controller
{
    public function index()
    {
        $turnos = TipoTurno::orderBy('hora_entrada')->get();
        return response()->json($turnos);
    }

    public function store(Request $request): JsonResponse
    {
        try {
            // Validar datos
            $validated = $request->validate([
                'nombre' => 'required|string|max:50|unique:tipo_turnos,nombre',
                'hora_entrada' => 'required|date_format:H:i',
                'hora_salida' => 'required|date_format:H:i|after:hora_entrada',
            ]);

            // Verificar que la diferencia sea de al menos 2 horas (para restar 1 de colación)
            $entrada = strtotime($validated['hora_entrada']);
            $salida = strtotime($validated['hora_salida']);
            $diferenciaHoras = ($salida - $entrada) / 3600;
            
            if ($diferenciaHoras < 2) {
                return response()->json([
                    'success' => false,
                    'message' => 'El turno debe durar al menos 2 horas (1 hora de trabajo + 1 hora de colación)'
                ], 422);
            }

            // Calcular horas trabajadas (restando 1 hora de colación)
            $horasTrabajadas = TipoTurno::calcularHorasTrabajadas(
                $validated['hora_entrada'], 
                $validated['hora_salida']
            );

            // Verificar si ya existe un turno con el mismo horario
            $existing = TipoTurno::where('hora_entrada', $validated['hora_entrada'])
                ->where('hora_salida', $validated['hora_salida'])
                ->first();

            if ($existing) {
                return response()->json([
                    'success' => false,
                    'message' => 'Ya existe un turno con este horario de entrada y salida'
                ], 422);
            }

            $turno = TipoTurno::create([
                'nombre' => $validated['nombre'],
                'hora_entrada' => $validated['hora_entrada'],
                'hora_salida' => $validated['hora_salida'],
                'horas_trabajadas' => $horasTrabajadas
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Turno creado correctamente',
                'turno' => $turno
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

    public function update(Request $request, $id): JsonResponse
    {
        try {
            $turno = TipoTurno::findOrFail($id);

            $validated = $request->validate([
                'nombre' => 'required|string|max:50|unique:tipo_turnos,nombre,' . $id,
                'hora_entrada' => 'required|date_format:H:i',
                'hora_salida' => 'required|date_format:H:i|after:hora_entrada',
            ]);

            // Verificar que la diferencia sea de al menos 2 horas
            $entrada = strtotime($validated['hora_entrada']);
            $salida = strtotime($validated['hora_salida']);
            $diferenciaHoras = ($salida - $entrada) / 3600;
            
            if ($diferenciaHoras < 2) {
                return response()->json([
                    'success' => false,
                    'message' => 'El turno debe durar al menos 2 horas (1 hora de trabajo + 1 hora de colación)'
                ], 422);
            }

            // Calcular horas trabajadas
            $horasTrabajadas = TipoTurno::calcularHorasTrabajadas(
                $validated['hora_entrada'], 
                $validated['hora_salida']
            );

            // Verificar duplicado excluyendo el registro actual
            $existing = TipoTurno::where('hora_entrada', $validated['hora_entrada'])
                ->where('hora_salida', $validated['hora_salida'])
                ->where('id', '!=', $id)
                ->first();

            if ($existing) {
                return response()->json([
                    'success' => false,
                    'message' => 'Ya existe otro turno con este horario de entrada y salida'
                ], 422);
            }

            $turno->update([
                'nombre' => $validated['nombre'],
                'hora_entrada' => $validated['hora_entrada'],
                'hora_salida' => $validated['hora_salida'],
                'horas_trabajadas' => $horasTrabajadas
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Turno actualizado correctamente',
                'turno' => $turno
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error: ' . $e->getMessage()
            ], 500);
        }
    }

    public function destroy($id): JsonResponse
    {
        try {
            $turno = TipoTurno::findOrFail($id);
            $turno->delete();

            return response()->json([
                'success' => true,
                'message' => 'Turno eliminado correctamente'
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al eliminar el turno'
            ], 500);
        }
    }
}