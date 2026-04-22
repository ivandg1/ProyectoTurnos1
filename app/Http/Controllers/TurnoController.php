<?php

namespace App\Http\Controllers;

use App\Models\Worker;
use App\Models\TurnoTemp;
use Illuminate\Http\Request;

class TurnoController extends Controller
{
    public function index()
    {
        $workers = Worker::all();
        $days = ['Lunes', 'Martes', 'Miércoles', 'Jueves', 'Viernes', 'Sábado', 'Domingo'];
        return view('turnos.index', compact('workers', 'days'));
    }

    public function preview(Request $request)
    {
        $semanas = $request->input('semanas', 4);
        return view('turnos.preview', compact('semanas'));
    }

    // API para obtener trabajadores con sus turnos rotados
    public function getRotacionData(Request $request)
    {
        try {
            $semanas = $request->input('semanas', 4);
            
            // Obtener trabajadores en el mismo orden que la grilla
            $workers = Worker::orderBy('nombre')->get();
            
            // Obtener asignaciones actuales desde turnos_temp
            $asignaciones = TurnoTemp::all();
            
            // Construir mapa de asignaciones por trabajador y día
            $asignacionesMap = [];
            foreach ($asignaciones as $asignacion) {
                $asignacionesMap[$asignacion->worker_id][$asignacion->dia] = [
                    'id' => $asignacion->tipo_turno_id,
                    'nombre' => $asignacion->nombre_turno,
                    'hora_entrada' => $asignacion->hora_entrada,
                    'hora_salida' => $asignacion->hora_salida,
                    'horas_trabajadas' => $asignacion->horas_trabajadas
                ];
            }
            
            // Obtener fechas de rotación
            $fechas = $this->obtenerFechasRotacion($semanas);
            
            // Generar rotación (arriba hacia abajo)
            $rotacionData = $this->generarRotacion($workers, $asignacionesMap, $fechas);
            
            return response()->json([
                'success' => true,
                'workers' => $rotacionData['workers'],
                'fechas' => $rotacionData['fechas'],
                'semanas' => $semanas,
                'total_turnos' => $rotacionData['total_turnos']
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al generar rotación: ' . $e->getMessage()
            ], 500);
        }
    }
    
    private function obtenerFechasRotacion($semanas)
    {
        $fechas = [];
        $hoy = new \DateTime();
        $proximoLunes = clone $hoy;
        $diaActual = (int)$hoy->format('w');
        $diasHastaLunes = ($diaActual === 0 ? 1 : 8 - $diaActual);
        $proximoLunes->modify("+{$diasHastaLunes} days");
        $proximoLunes->setTime(0, 0, 0);
        
        $totalDias = $semanas * 7;
        
        for ($i = 0; $i < $totalDias; $i++) {
            $fecha = clone $proximoLunes;
            $fecha->modify("+{$i} days");
            $fechas[] = $fecha;
        }
        
        return $fechas;
    }
    
    private function generarRotacion($workers, $asignacionesMap, $fechas)
    {
        $workersData = [];
        $totalTurnos = 0;
        $diasSemana = ['lunes', 'martes', 'miercoles', 'jueves', 'viernes', 'sabado', 'domingo'];
        
        foreach ($workers as $index => $worker) {
            $turnosPorFecha = [];
            
            foreach ($fechas as $fechaIndex => $fecha) {
                // Calcular qué trabajador origen le asigna el turno (rotación arriba hacia abajo)
                $semana = floor($fechaIndex / 7) + 1;
                $workerOrigenIndex = ($index + $semana - 1) % count($workers);
                $workerOrigen = $workers[$workerOrigenIndex];
                
                // Obtener el día de la semana en español
                $diaSemana = strtolower($fecha->format('l'));
                $diaMap = [
                    'monday' => 'lunes',
                    'tuesday' => 'martes',
                    'wednesday' => 'miercoles',
                    'thursday' => 'jueves',
                    'friday' => 'viernes',
                    'saturday' => 'sabado',
                    'sunday' => 'domingo'
                ];
                $dia = $diaMap[$diaSemana] ?? 'lunes';
                
                // Buscar turno asignado al trabajador origen para este día
                $turno = null;
                if (isset($asignacionesMap[$workerOrigen->id][$dia])) {
                    $turno = $asignacionesMap[$workerOrigen->id][$dia];
                    $totalTurnos++;
                }
                
                $turnosPorFecha[$fecha->format('Y-m-d')] = [
                    'fecha' => $fecha->format('Y-m-d'),
                    'dia_nombre' => ucfirst($dia),
                    'turno' => $turno,
                    'worker_origen' => $workerOrigen->nombre,
                    'semana' => $semana
                ];
            }
            
            $workersData[] = [
                'id' => $worker->id,
                'nombre' => $worker->nombre,
                'rut' => $worker->rut,
                'turnos' => $turnosPorFecha
            ];
        }
        
        // Formatear fechas para la respuesta
        $fechasFormateadas = [];
        foreach ($fechas as $fecha) {
            $fechasFormateadas[] = [
                'fecha' => $fecha->format('Y-m-d'),
                'dia_semana' => ucfirst($fecha->format('l')),
                'dia_numero' => $fecha->format('d/m')
            ];
        }
        
        return [
            'workers' => $workersData,
            'fechas' => $fechasFormateadas,
            'total_turnos' => $totalTurnos
        ];
    }
}