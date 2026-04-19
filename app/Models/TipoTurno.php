<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TipoTurno extends Model
{
    use HasFactory;

    protected $fillable = [
        'nombre',
        'hora_entrada',
        'hora_salida',
        'horas_trabajadas'
    ];

    // Calcular horas trabajadas automáticamente (restando 1 hora de colación)
    public static function calcularHorasTrabajadas($horaEntrada, $horaSalida)
    {
        $entrada = strtotime($horaEntrada);
        $salida = strtotime($horaSalida);
        $diferenciaHoras = ($salida - $entrada) / 3600;
        
        // Restar 1 hora de colación
        $horasTrabajadas = $diferenciaHoras - 1;
        
        return max(0, $horasTrabajadas);
    }
}