<?php

namespace App\Http\Controllers;

use App\Models\Worker;
use Illuminate\Http\Request;

class TurnoController extends Controller
{
    public function index()
    {
        // Obtener todos los trabajadores
        $workers = Worker::all();
        
        // Definir los días de la semana
        $days = ['Lunes', 'Martes', 'Miércoles', 'Jueves', 'Viernes', 'Sábado', 'Domingo'];
        
        return view('turnos.index', compact('workers', 'days'));
    }

    // Nuevo método para la vista previa de rotación
    public function preview()
    {
        $workers = Worker::all();
        $totalTrabajadores = $workers->count();
        
        return view('turnos.preview', compact('workers', 'totalTrabajadores'));
    }
}