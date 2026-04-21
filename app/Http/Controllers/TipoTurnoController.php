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
        // Validar datos sin la regla 'after' estándar
        $validated = $request->validate([
            'nombre' => 'required|string|max:50|unique:tipo_turnos,nombre',
            'hora_entrada' => 'required|date_format:H:i',
            'hora_salida' => 'required|date_format:H:i',
        ]);

        $horaEntrada = $validated['hora_entrada'];
        $horaSalida = $validated['hora_salida'];
        
        // Convertir horas a minutos para cálculos
        function timeToMinutes($time) {
            $parts = explode(':', $time);
            return intval($parts[0]) * 60 + intval($parts[1]);
        }
        
        $entradaMinutos = timeToMinutes($horaEntrada);
        $salidaMinutos = timeToMinutes($horaSalida);
        
        // Detectar si es turno nocturno (entrada después de las 20:00)
        $esNocturno = $entradaMinutos >= 20 * 60; // 20:00 = 1200 minutos
        
        // Calcular diferencia considerando cruce de día
        if ($salidaMinutos <= $entradaMinutos) {
            // Cruza la medianoche
            $diferenciaMinutos = (24 * 60 - $entradaMinutos) + $salidaMinutos;
        } else {
            $diferenciaMinutos = $salidaMinutos - $entradaMinutos;
        }
        
        $diferenciaHoras = $diferenciaMinutos / 60;
        
        // Validar según el tipo de turno
        if ($esNocturno) {
            // Turno nocturno: máximo 13 horas totales (12 de trabajo + 1 colación)
            if ($diferenciaHoras > 13) {
                return response()->json([
                    'success' => false,
                    'message' => 'Los turnos nocturnos no pueden exceder las 13 horas totales (12 horas de trabajo + 1 hora de colación)'
                ], 422);
            }
            if ($diferenciaHoras < 6) {
                return response()->json([
                    'success' => false,
                    'message' => 'Los turnos nocturnos deben durar al menos 6 horas totales'
                ], 422);
            }
        } else {
            // Turno diurno: mínimo 2 horas totales
            if ($diferenciaHoras < 2) {
                return response()->json([
                    'success' => false,
                    'message' => 'El turno debe durar al menos 2 horas totales (1 hora de trabajo + 1 hora de colación)'
                ], 422);
            }
        }
        
        // Verificar si ya existe un turno con el mismo horario
        $existing = TipoTurno::where('hora_entrada', $horaEntrada)
            ->where('hora_salida', $horaSalida)
            ->first();

        if ($existing) {
            return response()->json([
                'success' => false,
                'message' => 'Ya existe un turno con este horario de entrada y salida'
            ], 422);
        }

        // Calcular horas trabajadas (restando 1 hora de colación)
        $horasTrabajadas = $diferenciaHoras - 1;

        $turno = TipoTurno::create([
            'nombre' => $validated['nombre'],
            'hora_entrada' => $horaEntrada,
            'hora_salida' => $horaSalida,
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
            'hora_salida' => 'required|date_format:H:i',
        ]);

        $horaEntrada = $validated['hora_entrada'];
        $horaSalida = $validated['hora_salida'];
        
        // Convertir horas a minutos para cálculos
        function timeToMinutes($time) {
            $parts = explode(':', $time);
            return intval($parts[0]) * 60 + intval($parts[1]);
        }
        
        $entradaMinutos = timeToMinutes($horaEntrada);
        $salidaMinutos = timeToMinutes($horaSalida);
        
        // Detectar si es turno nocturno (entrada después de las 20:00)
        $esNocturno = $entradaMinutos >= 20 * 60;
        
        // Calcular diferencia considerando cruce de día
        if ($salidaMinutos <= $entradaMinutos) {
            $diferenciaMinutos = (24 * 60 - $entradaMinutos) + $salidaMinutos;
        } else {
            $diferenciaMinutos = $salidaMinutos - $entradaMinutos;
        }
        
        $diferenciaHoras = $diferenciaMinutos / 60;
        
        // Validar según el tipo de turno
        if ($esNocturno) {
            if ($diferenciaHoras > 13) {
                return response()->json([
                    'success' => false,
                    'message' => 'Los turnos nocturnos no pueden exceder las 13 horas totales (12 horas de trabajo + 1 hora de colación)'
                ], 422);
            }
            if ($diferenciaHoras < 6) {
                return response()->json([
                    'success' => false,
                    'message' => 'Los turnos nocturnos deben durar al menos 6 horas totales'
                ], 422);
            }
        } else {
            if ($diferenciaHoras < 2) {
                return response()->json([
                    'success' => false,
                    'message' => 'El turno debe durar al menos 2 horas totales (1 hora de trabajo + 1 hora de colación)'
                ], 422);
            }
        }
        
        // Verificar duplicado excluyendo el registro actual
        $existing = TipoTurno::where('hora_entrada', $horaEntrada)
            ->where('hora_salida', $horaSalida)
            ->where('id', '!=', $id)
            ->first();

        if ($existing) {
            return response()->json([
                'success' => false,
                'message' => 'Ya existe otro turno con este horario de entrada y salida'
            ], 422);
        }

        // Calcular horas trabajadas (restando 1 hora de colación)
        $horasTrabajadas = $diferenciaHoras - 1;

        $turno->update([
            'nombre' => $validated['nombre'],
            'hora_entrada' => $horaEntrada,
            'hora_salida' => $horaSalida,
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