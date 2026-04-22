<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('turnos_temp', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('worker_id');
            $table->string('dia', 20);
            $table->unsignedBigInteger('tipo_turno_id')->nullable();
            $table->string('hora_entrada', 5)->nullable();
            $table->string('hora_salida', 5)->nullable();
            $table->string('nombre_turno', 100)->nullable();
            $table->decimal('horas_trabajadas', 5, 2)->default(0);
            $table->timestamps();
            
            // Índices
            $table->index(['worker_id', 'dia']);
            $table->foreign('worker_id')->references('id')->on('workers')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('turnos_temp');
    }
};
