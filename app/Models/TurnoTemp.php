<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TurnoTemp extends Model
{
    use HasFactory;

    protected $table = 'turnos_temp';

    protected $fillable = [
        'worker_id',
        'dia',
        'tipo_turno_id',
        'hora_entrada',
        'hora_salida',
        'nombre_turno',
        'horas_trabajadas'
    ];

    public function worker()
    {
        return $this->belongsTo(Worker::class);
    }
}