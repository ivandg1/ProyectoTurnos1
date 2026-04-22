<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TurnoRotacionTemp extends Model
{
    use HasFactory;

    protected $table = 'turnos_rotaciones_temp';

    protected $fillable = [
        'worker_id',
        'fecha_asignada',
        'dia',
        'tipo_turno_id',
        'hora_entrada',
        'hora_salida',
        'nombre_turno',
        'horas_trabajadas',
        'semana',
        'parametros_rotacion'
    ];

    protected $casts = [
        'fecha_asignada' => 'date',
        'parametros_rotacion' => 'array',
        'horas_trabajadas' => 'decimal:2'
    ];

    public function worker()
    {
        return $this->belongsTo(Worker::class);
    }
}
