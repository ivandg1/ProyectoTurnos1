<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('turnos_rotaciones_temp', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('worker_id');
            $table->date('fecha_asignada'); // Fecha específica del turno
            $table->string('dia', 20); // Lunes, Martes, etc.
            $table->unsignedBigInteger('tipo_turno_id')->nullable();
            $table->string('hora_entrada', 5)->nullable();
            $table->string('hora_salida', 5)->nullable();
            $table->string('nombre_turno', 100)->nullable();
            $table->decimal('horas_trabajadas', 5, 2)->default(0);
            $table->integer('semana')->nullable(); // Número de semana (1, 2, 3...)
            $table->json('parametros_rotacion')->nullable(); // Guardar parámetros usados
            $table->timestamps();
            
            // Índices para búsqueda rápida
            $table->index(['worker_id', 'fecha_asignada']);
            $table->index(['fecha_asignada', 'dia']);
            $table->foreign('worker_id')->references('id')->on('workers')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('turnos_rotaciones_temp');
    }
};
