<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tipo_turnos', function (Blueprint $table) {
            $table->id();
            $table->string('nombre', 50);
            $table->time('hora_entrada');
            $table->time('hora_salida');
            $table->integer('horas_trabajadas');
            $table->timestamps();
            
            // Índice único para evitar duplicados
            $table->unique(['hora_entrada', 'hora_salida']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tipo_turnos');
    }
};